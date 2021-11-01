<?php

namespace JBtje\VtigerLaravel;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Laravel wrapper for the VTgier API
 *
 * Class Vtiger
 * @package JBtje\Vtiger-Laravel
 */
class Vtiger
{
    protected string $url;
    protected string $username;
    protected string $accessKey;
    protected string $persistConnection;
    protected Client $guzzleClient;
    protected int    $maxRetries;

    /**
     * Vtiger constructor.
     */
    public function __construct()
    {
        // Obtain the connection information.
        $this->url               = config( 'vtiger.url' );
        $this->username          = config( 'vtiger.username' );
        $this->accessKey         = config( 'vtiger.accesskey' );
        $this->persistConnection = config( 'vtiger.persistconnection' );
        $this->maxRetries        = config( 'vtiger.max_retries' );

        // Initiate a new GuzzleHttp Client.
        $this->guzzleClient = new Client( ['http_errors' => false, 'verify' => false] );
    }

    /**
     * Override configured connection details.
     *
     * @param string $url
     * @param string $username
     * @param string $accessKey
     *
     * @return $this
     */
    public function connection( string $url, string $username, string $accessKey )
    {
        $this->url       = $url;
        $this->username  = $username;
        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * Get the session id for a login either from a stored session id or fresh from the API
     *
     * @return string|null
     * @throws GuzzleException
     */
    protected function sessionId()
    {
        // Get the sessionData from the cache
        $sessionData = json_decode( Cache::get( 'vtiger_laravel' ) );

        if( !isset( $sessionData, $sessionData->expireTime, $sessionData->token ) || $sessionData->expireTime < time() || empty( $sessionData->token ) ) {
            $sessionData = $this->storeSession();
        }

        if( isset( $sessionData->sessionid ) ) {
            return $sessionData->sessionid;
        }

        return $this->login( $sessionData );
    }

    /**
     * Login to the VTiger API to get a new session
     *
     * @param $sessionData
     *
     * @return null
     * @throws GuzzleException
     */
    protected function login( $sessionData )
    {
        $sessionId = NULL;
        $token     = $sessionData->token;

        // Create unique key using combination of challenge token and access key
        $generatedKey = md5( $token . $this->accessKey );

        $tryCounter = 1;

        do {
            // login using username and accesskey
            $response = $this->guzzleClient->request( 'POST', $this->url, [
                'form_params' => [
                    'operation' => 'login',
                    'username'  => $this->username,
                    'accessKey' => $generatedKey,
                ],
            ] );

            // decode the response
            $loginResult = $this->_processResponse( $response );
            $tryCounter++;
        } while( !isset( $loginResult->success ) && $tryCounter <= $this->maxRetries );

        if( $tryCounter >= $this->maxRetries ) {
            throw new Exception( 'Could not complete login request within ' . $this->maxRetries . ' tries' );
        }

        // Check if the response is invalid, or not successful.
        if( $response->getStatusCode() !== 200 || !$loginResult->success ) {
            if( $loginResult->error->code == 'INVALID_USER_CREDENTIALS' || $loginResult->error->code == 'INVALID_SESSIONID' ) {
                Cache::has( 'vtiger_laravel' ) && Cache::forget( 'vtiger_laravel' );
            }
            else {
                $this->_processResponse( $response );
            }
        }
        else {
            // Response is valid.
            $sessionId = $loginResult->result->sessionName;

            if( Cache::has( 'vtiger_laravel' ) ) {
                $json            = json_decode( Cache::pull( 'vtiger_laravel' ) );
                $json->sessionid = $sessionId;
                Cache::forever( 'vtiger_laravel', json_encode( $json ) );
            }
            else {
                throw new Exception( 'Laravel cache key "vtiger_laravel" does not exist.' );
            }
        }

        return $sessionId;
    }

    /**
     * Store a new session if needed
     *
     * @return object
     * @throws GuzzleException
     */
    protected function storeSession(): object
    {
        $updated = $this->getToken();

        $output = (object)$updated;
        Cache::forever( 'vtiger_laravel', json_encode( $output ) );

        return $output;
    }

    /**
     * Get a new access token from the VTiger API
     *
     * @return array
     * @throws GuzzleException
     */
    protected function getToken(): array
    {
        // perform API GET request
        $tryCounter = 1;
        do {
            $response = $this->guzzleClient->request( 'GET', $this->url, [
                'query' => [
                    'operation' => 'getchallenge',
                    'username'  => $this->username,
                ],
            ] );

            $tryCounter++;
        } while( !isset( $this->_processResponse( $response )->success ) && $tryCounter <= $this->maxRetries );

        if( $tryCounter >= $this->maxRetries ) {
            throw new Exception( 'Could not complete get token request within ' . $this->maxRetries . ' tries' );
        }

        // decode the response
        $challenge = $this->_processResponse( $response );

        return [
            'token'      => $challenge->result->token,
            'expireTime' => $challenge->result->expireTime,
        ];
    }

    /**
     * Logout from the VTiger API
     *
     * @param string $sessionId
     *
     * @return bool|mixed
     * @throws GuzzleException
     */
    protected function close( string $sessionId )
    {
        if( $this->persistConnection ) {
            return true;
        }

        // send a request to close current connection
        $response = $this->guzzleClient->request(
            'POST',
            $this->url,
            [
                'query' => [
                    'operation'   => 'logout',
                    'sessionName' => $sessionId,
                ],
            ]
        );

        return $this->_processResponse( $response );
    }

    /**
     * Query the VTiger API with the given query string
     *
     * @param string $query
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function query( string $query )
    {
        $sessionId = $this->sessionId();

        // send a request using a database query to get back any matching records
        $response = $this->guzzleClient->request( 'GET', $this->url, [
            'query' => [
                'operation'   => 'query',
                'sessionName' => $sessionId,
                'query'       => $query,
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    public function search( $query, $quote = true )
    {
        $bindings    = $query->getBindings();
        $queryString = $query->toSQL();

        foreach( $bindings as $binding ) {
            if( $quote ) {
                $queryString = preg_replace( '/\?/', DB::connection()->getPdo()->quote( $binding ), $queryString, 1 );
            }
            else {
                $queryString = preg_replace( '/\?/', $binding, $queryString, 1 );
            }
        }

        // In the event there is an offset, append it to the front of the limit
        // Vtiger does not support the offset keyword
        $matchOffset = [];
        $matchLimit  = [];
        if( preg_match( '/(\s[o][f][f][s][e][t]) (\d*)/', $queryString, $matchOffset ) && preg_match( '/(\s[l][i][m][i][t]) (\d*)/', $queryString, $matchLimit ) ) {
            $queryString = preg_replace( '/(\s[o][f][f][s][e][t]) (\d*)/', '', $queryString );
            $queryString = preg_replace( '/(\s[l][i][m][i][t]) (\d*)/', '', $queryString );
            $queryString = $queryString . ' limit ' . $matchOffset[2] . ',' . $matchLimit[2];
        }

        // Remove the backticks and add semicolon
        $queryString = str_replace( '`', '', $queryString ) . ';';

        return $this->query( $queryString );
    }

    public function listTypes()
    {
        $sessionId = $this->sessionId();

        // Lookup the data
        // send a request to retrieve all list types
        $response = $this->guzzleClient->request( 'GET', $this->url, [
            'query' => [
                'operation'   => 'listtypes',
                'sessionName' => $sessionId,
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    public function lookup( $dataType, $value, $module, array $columns )
    {
        $sessionId = $this->sessionId();

        // Update columns into the proper format
        $columnsText = '';
        foreach( $columns as $column ) {
            $columnsText .= '"' . $column . '",';
        }

        // Trim the last comma from the string
        $columnsText = substr( $columnsText, 0, (strlen( $columnsText ) - 1) );

        // Lookup the data
        // send a request to retrieve a record
        $response = $this->guzzleClient->request( 'GET', $this->url, [
            'query' => [
                'operation'   => 'lookup',
                'sessionName' => $sessionId,
                'type'        => $dataType,
                'value'       => $value,
                'searchIn'    => '{"' . $module . '":[' . $columnsText . ']}',
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * Retrieve a record from the VTiger API
     * Format of id must be {module_code}x{item_id}, e.g 4x12
     *
     * @param string $id
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function retrieve( string $id )
    {
        $sessionId = $this->sessionId();

        // send a request to retrieve a record
        $response = $this->guzzleClient->request( 'GET', $this->url, [
            'query' => [
                'operation'   => 'retrieve',
                'sessionName' => $sessionId,
                'id'          => $id,
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * Create a new entry in the VTiger API
     *
     * Make sure to fill all mandatory fields.
     *
     * @param       $elem
     * @param array $data
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function create( $elem, array $data )
    {
        $sessionId = $this->sessionId();

        // send a request to create a record
        $response = $this->guzzleClient->request( 'POST', $this->url, [
            'form_params' => [
                'operation'   => 'create',
                'sessionName' => $sessionId,
                'element'     => json_encode( $data ),
                'elementType' => $elem,
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * Update an entry in the database from the given object
     *
     * The object should be an object retreived from the database and then altered
     *
     * @param $object
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function update( $object )
    {
        $sessionId = $this->sessionId();

        // send a request to update a record
        $response = $this->guzzleClient->request( 'POST', $this->url, [
            'form_params' => [
                'operation'   => 'update',
                'sessionName' => $sessionId,
                'element'     => json_encode( $object ),
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * Delete from the database using the given id
     * Format of id must be {moudler_code}x{item_id}, e.g 4x12
     *
     * @param string $id
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function delete( string $id )
    {
        $sessionId = $this->sessionId();

        // send a request to delete a record
        $response = $this->guzzleClient->request( 'POST', $this->url, [
            'form_params' => [
                'operation'   => 'delete',
                'sessionName' => $sessionId,
                'id'          => $id,
            ],
        ] );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * Describe an element from the vTiger API from the given element name
     *
     * @param string $elementType
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function describe( string $elementType )
    {
        $sessionId = $this->sessionId();

        // send a request to describe a module (which returns a list of available fields) for a Vtiger module
        $response = $this->guzzleClient->request(
            'GET',
            $this->url,
            [
                'query' => [
                    'operation'   => 'describe',
                    'sessionName' => $sessionId,
                    'elementType' => $elementType,
                ],
            ]
        );

        $this->close( $sessionId );

        return $this->_processResponse( $response );
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    protected function _processResponse( $response )
    {
        // decode the response
        if( $response->getStatusCode() == 200 ) {
            if( !empty( $response->getBody()->getContents() ) ) {
                $response->getBody()->rewind();
                $data = json_decode( $response->getBody()->getContents() );
            }
            else {
                $data = json_decode( $response->getBody() );
            }
        }
        else {
            $data = $response;
        }

        return $data;
    }
}
