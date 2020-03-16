<?php
/**
 * @author b1tc0re <b1tc0re@yandex.com>
 */

namespace Nutnet\RKeeper7Api\Logical;

use Nutnet\RKeeper7Api\Client;
use Nutnet\RKeeper7Api\Exceptions\RequestFailedException;
use Nutnet\RKeeper7Api\Requests\AuthorizationRequest;
use Nutnet\RKeeper7Api\Requests\EditHolderRequest;
use Nutnet\RKeeper7Api\Requests\GetCardInfoRequest;
use Nutnet\RKeeper7Api\Requests\RegistrationRequest;

/**
 * Class AuthorizeLogic
 * @package Nutnet\RKeeper7Api\Logical
 */
class AuthorizeLogic extends BaseLogical
{

    public function Authorize($card, $phone, $password)
    {
        try
        {
            $params['Card_Code']    = $card;
            $params['Phone']        = $phone;
            $params['Password']     = $password;
            $params['Include']      = 'Holder';
            $response = $this->client->call(new AuthorizationRequest($params));

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


        return isset($response['@attributes']['Session']) ? $response['@attributes']['Session'] : false;
    }
}
