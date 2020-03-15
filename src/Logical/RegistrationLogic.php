<?php
/**
 * @author b1tc0re <b1tc0re@yandex.com>
 */

namespace Nutnet\RKeeper7Api\Logical;

use Nutnet\RKeeper7Api\Client;
use Nutnet\RKeeper7Api\Exceptions\RequestFailedException;
use Nutnet\RKeeper7Api\Requests\EditHolderRequest;
use Nutnet\RKeeper7Api\Requests\GetCardInfoRequest;
use Nutnet\RKeeper7Api\Requests\RegistrationRequest;

/**
 * Class RegistrationLogic
 * @package Nutnet\RKeeper7Api\Logical
 */
class RegistrationLogic extends BaseLogical
{

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
     * @return bool
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
            $params['Include'] = 'Code_Timeout';
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
                'Login'     => $params['Login'],
                'Include'   => 'Holder, Account, Account_Available'
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

        $this->client->call(new EditHolderRequest(array(
            'Holder' => array(
                'children' => array(
                    'Holder_ID'             => $response['Holder_ID'],
                    'Verification'          => 'Yes',
                    'Smoke'                 => 'Yes',
                    'Auto_Change_Levels'    => 'True',
                    'Source'                => 'Online registration',
                    'Accounts' => array(
                        'children' => array(
                            'Account' => array(
                                'children' => array(
                                    'Account_Number'     => (string)$response['Accounts']['Account'][1]->Account_Number[0],
                                    'Auto_Change_Levels' => 'False',
                                )
                            )
                        )
                    )
                )
            )
        )));

        return count($response) === 0;
    }
}
