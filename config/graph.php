<?php
/**
 *  Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 *
 *  PHP version 5
 *
 *  @category Code_Sample
 *  @package  php-connect-sample
 *  @author   Microsoft
 *  @license  MIT License
 *  @link     http://github.com/microsoftgraph/php-connect-sample
 */

namespace Microsoft\Graph\Connect;

/**
 *  Stores constant and configuration values used through the app
 *
 *  @class    Constants
 *  @category Code_Sample
 *  @package  php-connect-sample
 *  @author   Microsoft
 *  @license  MIT License
 *  @link     http://github.com/microsoftgraph/php-connect-sample
 */
class Constants
{

    const CLIENT_ID          = 'f313dc1a-6235-4b1f-8333-67abde6cc805';
    const CLIENT_SECRET      = '0T7jPPAhw9eGkFbxyv5ghPwh4vouYwAT9RMRLLUF0+Y=';
    const REDIRECT_URI       = 'http://localhost/oauth.php';
    const AUTHORITY_URL      = 'https://login.microsoftonline.com/common';
    const AUTHORIZE_ENDPOINT = '/oauth2/authorize';
    const TOKEN_ENDPOINT     = '/oauth2/token';
    const RESOURCE_ID        = 'https://graph.microsoft.com';
    const AADGraph           = 'https://graph.windows.net';
    const BINGMAPKEY         = '';
    const SENDMAIL_ENDPOINT  = '/v1.0/me/sendmail';
    const SCOPES             = 'profile openid email offline_access User.Read Mail.Send';



}
