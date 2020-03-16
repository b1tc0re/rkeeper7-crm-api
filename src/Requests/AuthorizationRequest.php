<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 30.05.17
 */

namespace Nutnet\RKeeper7Api\Requests;

/**
 * Class GetCardInfoRequest
 * @package Nutnet\RKeeper7Api\Requests
 *
 * <?xml version="1.0" encoding="Windows-1251" standalone="yes" ?>
<Message Action="Authorization" Terminal_Type="2" Global_Type="ABC" Unit_ID="1"
User_ID="1">
<Card_Code>123456789</Card_Code>
<Phone>+7(123)456-78-90</Phone>
<EMail>support@ucs.ru</EMail>
<Password>1</Password>
<Include></Include>
</Message>
 */
class AuthorizationRequest extends RequestWithParamsAbstract
{
    /**
     * @return string
     */
    public function getAction()
    {
        return 'Authorization';
    }
}
