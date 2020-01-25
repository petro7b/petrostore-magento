<?php

class Cminds_Marketplace_RestController extends Cminds_Marketplace_Controller_Action {
    public function indexAction() {

        $params = array(
            'siteUrl'         => 'http://magento.dev/oauth',
            'requestTokenUrl' => 'http://magento.dev/oauth/initiate',
            'accessTokenUrl'  => 'http://magento.dev/oauth/token',
            'authorizeUrl'    => 'http://magento.dev/admin/oAuth_authorize',
            'consumerKey'     => 'a76176f6a5bce8f09791f7808047e5b7',
            'consumerSecret'  => '40fd009096eaaae630569e93f2717bc5',
            'callbackUrl'     => 'http://magento.dev/marketplace/rest/callback',
        );

        $consumer = new Zend_Oauth_Consumer( $params );
        $requestToken = $consumer->getRequestToken();
        $session = Mage::getSingleton( 'core/session' );
        $session->setRequestToken( serialize( $requestToken ) );
        $consumer->redirect();

        return;
    }

    public function callbackAction() {
        ini_set('xdebug.max_nesting_level', 500);
        $params = array(
            'siteUrl'         => 'http://magento.dev/oauth',
            'requestTokenUrl' => 'http://magento.dev/oauth/initiate',
            'accessTokenUrl'  => 'http://magento.dev/oauth/token',
            'consumerKey'     => 'a76176f6a5bce8f09791f7808047e5b7',
            'consumerSecret'  => '40fd009096eaaae630569e93f2717bc5'
        );

        $session = Mage::getSingleton( 'core/session' );
        $requestToken = unserialize( $session->getRequestToken() );
        $consumer = new Zend_Oauth_Consumer( $params );
        $acessToken = $consumer->getAccessToken(filter_input_array(INPUT_GET), $requestToken );
        $restClient = $acessToken->getHttpClient( $params );
        $restClient->setUri( 'http://magento.dev/api/rest/store_products/137' );
        $restClient->setHeaders( 'Accept', 'application/json' );
        $restClient->setMethod( Zend_Http_Client::GET );
        $response = $restClient->request();
        Zend_Debug::dump( $response->getBody() );
        return;
    }
}