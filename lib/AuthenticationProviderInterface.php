<?php

namespace Szamlakozpont;

interface AuthenticationProviderInterface
{
    public function getApiKey(): string;
    
    public function getTaxNumber(): string;
    
    public function getEmail(): string;
}