<?php
/**
 * @author b1tc0re <b1tc0re@yandex.com>
 */

namespace Nutnet\RKeeper7Api\Logical;

use Nutnet\RKeeper7Api\Client;

/**
 * Class RegistrationLogic
 * @package Nutnet\RKeeper7Api\Logical
 */
class BaseLogical
{
    /**
     * Клиент для отправки запросов к RK7 серверу
     * @var Client
     */
    protected $client;

    /**
     * Сообшение ошибок
     * @var array
     */
    protected $errorsMessage = array();

    /**
     * RegistrationLogic constructor.
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Вернуть сообшение с ошибками
     * @return array
     */
    public function getErrors()
    {
        return $this->errorsMessage;
    }

    /**
     * Вывод ошибок
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function error_string($prefix = '', $suffix = '')
    {
        // No errors, validation passes!
        if (count($this->errorsMessage) === 0) {
            return '';
        }

        // Generate the error string
        $str = '';
        foreach ($this->errorsMessage as $val)
        {
            if ($val !== '')
            {
                $str .= $prefix.$val.$suffix."\n";
            }
        }

        return $str;
    }

    /**
     * function xml2array
     *
     * This function is part of the PHP manual.
     *
     * The PHP manual text and comments are covered by the Creative Commons
     * Attribution 3.0 License, copyright (c) the PHP Documentation Group
     *
     * @author  k dot antczak at livedata dot pl
     * @date    2011-04-22 06:08 UTC
     * @link    http://www.php.net/manual/en/ref.simplexml.php#103617
     * @license http://www.php.net/license/index.php#doc-lic
     * @license http://creativecommons.org/licenses/by/3.0/
     * @license CC-BY-3.0 <http://spdx.org/licenses/CC-BY-3.0>
     */
    protected function xml2array( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;

        return $out;
    }

    /**
     * Проверить если запрос выполнен
     * @param array $response
     * @return bool
     */
    protected function hasSuccessRequest(array $response)
    {
        if( array_key_exists('@attributes', $response) && array_key_exists('ErrorCode', $response['@attributes']) )
        {
            $this->errorsMessage[$response['@attributes']['ErrorCode']] =  $response['@attributes']['ErrorText'];
            return false;
        }

        return true;
    }
}
