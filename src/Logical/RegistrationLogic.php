<?php
/**
 * @author b1tc0re <b1tc0re@yandex.com>
 */

namespace Nutnet\RKeeper7Api\Logical;

use Nutnet\RKeeper7Api\Client;
use Nutnet\RKeeper7Api\Exceptions\RequestFailedException;
use Nutnet\RKeeper7Api\Requests\GetCardInfoRequest;
use Nutnet\RKeeper7Api\Requests\RegistrationRequest;

/**
 * Class RegistrationLogic
 * @package Nutnet\RKeeper7Api\Logical
 */
class RegistrationLogic
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
     * Проверка если карта пригодна для регистрации
     * Если карта не привязана к владельзу значит она добавлена в систему и выдана клиенту для регистрации
     * @param string $card Номер карты
     * @return bool
     */
    protected function issetCard($card)
    {
        try
        {
            $response = $this->client->call(new GetCardInfoRequest(array(
                'Card_Code' => $card,
                'Include'   => 'Holder'
            )));

            $response = $this->xml2array($response);
        }
        catch (RequestFailedException $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }
        catch (\Exception $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }

        if( $this->hasSuccessRequest($response) === false ) {
            return false;
        }

        if( array_key_exists('Holder_ID', $response['Card']) && is_array($response['Card']['Holder_ID']) ) {
            return true;
        }

        return false;
    }

    /**
     * Регистрация карт
     * @param array $params - Параметры регистрации
     */
    public function Registration(array $params)
    {
        if( $this->issetCard($params['Card_Code']) === false )
        {
            $this->errorsMessage[] = 'Карта не найдена в системе или уже зарегистрирована.';
            return false;
        }

        try
        {
            $response = $this->client->call(new RegistrationRequest($params));
            $response = $this->xml2array($response);
        }
        catch (RequestFailedException $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }
        catch (\Exception $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }

        if( $this->hasSuccessRequest($response) === false ) {
            return false;
        }

        if( array_key_exists('Code_Timeout', $response) )
        {
            return true;
        }

        $this->errorsMessage[] = 'Не удалось выполнить операцию.';
        return false;
    }

    /**
     * Подтверждение регистрации
     * @param string $code  - Код подтверждения
     * @param array $params - Параметры запроса регистрации
     * @return bool
     */
    public function RegistrationConfirm($code, array $params)
    {
        try
        {
            $response = $this->client->call(new RegistrationRequest(array(
                'Auth_Code' => $code,
                'Card_Code' => $params['Card_Code'],
                'Login'     => $params['Login'],
                'Include'   => ''
            )));

            $response = $this->xml2array($response);
        }
        catch (RequestFailedException $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }
        catch (\Exception $e)
        {
            $this->errorsMessage[] = 'Не удалось выполнить операцию.';
            return false;
        }

        if( $this->hasSuccessRequest($response) === false ) {
            return false;
        }

        return count($response) === 0;
    }

    public function getErrors()
    {
        return $this->errorsMessage;
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
            $this->errorsMessage[] =  $response['@attributes']['ErrorText'];
            return false;
        }

        return true;
    }
}
