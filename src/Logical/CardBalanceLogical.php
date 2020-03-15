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
 * Class CardBalanceLogical
 * @package Nutnet\RKeeper7Api\Logical
 */
class CardBalanceLogical extends BaseLogical
{
    /**
     *
     * @param string $code
     * @return array|bool
     */
    public function getCardBalance($code)
    {
        try
        {
            $response = $this->client->call(new GetCardInfoRequest(array(
                'Card_Code' => $code,
                'Include'   => 'Holder,Account'
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

        return $response;
    }
}
