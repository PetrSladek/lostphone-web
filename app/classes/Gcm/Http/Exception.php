<?php
namespace Gcm\Http;

class Exception extends \Exception {}

class LogicException extends Exception {}
class RuntimeException extends Exception {}

class IlegalApiKeyException extends LogicException {}
