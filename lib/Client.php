<?php

namespace Szamlakozpont;

use Psr\Log\LoggerInterface;

class Client
{
    private $test;
    private $logger;
    private $authenticationProvider;
    
    const XML_TYPE_INVOICE_IN = 'SZAMLA-IN';
    const XML_TYPE_PAYOUT = 'KIEGY-IN';
    
    const OUTPUT_FORMAT_XML = 'XML';
    const OUTPUT_FORMAT_FORMATTED = 'FORMATTED';
    
    const REQUEST_XMLREQUEST = 'XMLRequest';

    public function __construct(AuthenticationProviderInterface $authenticationProvider, bool $test = false, ?LoggerInterface $logger = null)
    {
        $this->authenticationProvider = $authenticationProvider;
        $this->test = $test;
        $this->logger = $logger;

        if (!class_exists('\SoapClient')) {
            throw new \LogicException('Soap extension is required for Szamlakozpont E-Invoice Provider');
        }
        
        $this->client = new \SoapClient($this->getWSDLURL(), [
            'soap_version' => SOAP_1_2,
            'trace' => true,
            'exceptions'  => true
        ]);
    }
    
    private function buildArguments(string $request, string $xmlData, string $xmlType): array
    {
        if ($request === self::REQUEST_XMLREQUEST) {
            return [
                'XMLData' => $xmlData,
                'XMLType' => $xmlType.';OUT:XML',
                'Adoszam' => $this->authenticationProvider->getTaxNumber(),
                'email' => $this->authenticationProvider->getEmail(),
                'Azonosito' => $this->authenticationProvider->getApiKey(),
            ];
        }
        
        throw new \LogicException('unknown request type: '.$request);
    }
    
    public function sendXmlRequest(string $xmlData, string $xmlType)
    {
        $arguments = $this->buildArguments(self::REQUEST_XMLREQUEST, $xmlData, $xmlType);
        
        if ($this->logger) {
            $this->logger->debug('XMLRequest will call', $arguments);
        }
        
        $result = $this->client->XmlRequest($arguments);

        if ($this->logger) {
            $this->logger->debug('XMLRequest did call', $result);
        }
        
        return $result;
    }
    
    private function getWsdlUrl()
    {
        return 'http'.($this->test ? '' : 's').'://xmlservice.szamlakozpont.hu/XMLService.asmx?wsdl';
    }
    
}