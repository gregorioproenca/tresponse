<?php

namespace TResponse;

use Throwable;
use TResponse\TResponseException;

/**
 * Manipula erros e respostas
 * @version 1.0.007
 */
class TResponse
{
    /**
     * Instância da classe
     *
     * @var mixed
     */
    private static $instance = null;

    /**
     * Indica erro na resposta
     *
     * @var bool
     */
    public $error;

    /**
     * Código do status da resposta
     *
     * @var int
     */
    private $status;

    /**
     * Mensagem da resposta
     *
     * @var string
     */
    public $message;

    public const DEFAULT_SUCCESS_MESSAGE   = 'Ação realizada com sucesso!';
    public const DEFAULT_ERROR_MESSAGE     = 'Não foi possível realizar esta ação.';
    public const DEFAULT_EXCEPTION_MESSAGE = 'Ocorreu um erro inesperado ao realizar esta ação, por favor tente novamente mais tarde.';
    private const DEFAULT_ATTRIBUTES       = ["error", "message", "status", "file", "line", "trace", "type"];
    private const VALID_HTTP_STATUS_CODES  = [
        100, 101,
        200, 201, 202, 203, 204, 205, 206,
        300, 301, 302, 303, 304, 305, 306, 307,
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
        500, 501, 502, 503, 504, 505,
    ];

    private function __construct()
    {
        $this->error   = false;
        $this->status  = 200;
        $this->message = '_';
    }

    /**
     * Impossibilita da classe ser clonada
     */
    public function __clone()
    {
        throw new TResponseException('You cannot clone this class.');
    }

    /**
     * Retorna a unica instância
     *
     * @return \TResponse\TResponse
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new TResponse;
        }

        return self::$instance;
    }

    /**
     * Se retornar a classe em uma sentença string(echo, etc)
     * Printar 'Erro' ou 'Sucesso'
     * ou a primeira mensagem inserida na classe caso error = true.
     *
     * @return string
     */
    public function __toString()
    {
        $instance = self::instance();
        if ($instance->getError()) {
            $string = self::DEFAULT_ERROR_MESSAGE;
            if (!empty($instance->message)) {
                $string = $instance->message;
            }

        } else {
            $string = self::DEFAULT_SUCCESS_MESSAGE;
        }
        return $string;
    }

    /**
     * Retorna a mensagem
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Seta o estado da classe como error = false e seta mensagens.
     *
     * @param array/string Array de strings ou uma string
     * @return \TResponse\TResponse
     */
    public static function success($message = '')
    {
        $instance = self::instance();

        $instance->setError(false);
        $instance->setStatus(200);
        $instance->setMessage($message);

        return $instance;
    }

    /**
     * Seta o estado da classe como error = true e seta mensagens
     * ou insere uma exception na resposta caso esteja em ambiente de produção
     *
     * @param array|string|\Throwable Array de strings ou uma string ou um objeto Throwable
     * @return \TResponse\TResponse
     */
    public static function error($message = '', $status = 500)
    {
        $instance = self::instance();

        $instance->setError(true);

        if (self::isException($message)) {
            $instance->setException($message);
        } else {
            $instance->unsetCustomAttributes();
            $instance->setMessage($message);
            $instance->setStatus($status);
        }
        return $instance;
    }

    /**
     * Seta o estado da classe como error = $success e seta mensagens
     * ou insere uma exception na resposta caso esteja em ambiente de produção
     *
     * @param mixed $success Variavel a ser avaliada como boolean
     * @param string $success_message Mensagem de sucesso
     * @param string $error_message Mensagem de erro
     * @param int $error_code Código de status a ser usado caso $success for falso
     * @param bool $strict Usar identico ao invés de igual
     *
     * @return \TResponse\TResponse
     */
    public static function handle($success, $success_message = '', $error_message = '', int $error_code = 500, bool $strict = false)
    {
        $instance = self::instance();

        if (self::isException($success)) {

            $instance->setException($success);

        } else {

            $instance->handleError($success, $strict);

            $status = self::getErrorCode($success, $error_code, $strict);
            $instance->setStatus($status);

            if ($instance->error) {
                $instance->setMessage($error_message);
            } else {
                $instance->setMessage($success_message);
            }

        }

        return $instance;
    }

    /**
     * Retorna o código de status de acordo com o $success e $strict

     * @param mixed $success Variavel a ser avaliada como boolean
     * @param int $error_code Código de status a ser usado caso retorne falso
     * @param bool $strict Usar identico ao invés de igual
     * @return int
     */
    private static function getErrorCode($success, int $error_code = 500, bool $strict)
    {
        if ($strict) {
            return (($success === true) ? 200 : $error_code);
        } else {
            return ($success ? 200 : $error_code);
        }
    }

    /**
     * Seta o $error da classe
     *
     * @param mixed $error Representa se houve erros
     * @param bool $strict Usar igualdade estrita (idêntico)
     * @return void
     */
    private function handleError($success, bool $strict = false)
    {
        if ($strict) {
            $this->setError($success !== true);
        } else {
            $this->setError($success != true);
        }
    }

    /**
     * Seta o $error da classe
     *
     * @param mixed $error Representa se houve erros
     * @param bool $strict Usar igualdade estrita (idêntico)
     * @return void
     */
    private function setError($error, bool $strict = false)
    {
        if ($strict) {
            $this->error = ($error === true);
        } else {
            $this->error = ($error == true);
        }
    }

    /**
     * Retorna o $error da classe
     *
     * @return bool
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Seta o código de status da classe
     *
     * @param int $status_code
     */
    private function setStatus(int $error_code)
    {
        info($error_code);
        if (in_array($error_code, self::VALID_HTTP_STATUS_CODES, true)) {
            $this->status = $error_code;
        } else {
            // throw new TResponseException("HTTP Status Code "$error_code" inserido não é válido.", 500);
            $this->status = 500;
        }
    }

    /**
     * Retorna o $status da classe
     *
     * @return int
     */
    public static function status()
    {
        return self::instance()->getStatus();
    }

    /**
     * Retorna o $status da classe
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Define uma exception na classe
     *
     * @param \Throwable Exception a ser reportada
     * @return \TResponse\TResponse self
     */
    public static function exception(Throwable $exception)
    {
        $instance = self::instance();

        $instance->error($exception);

        $instance->unsetCustomAttributes();

        return $instance;
    }

    /**
     * Remove atributos não padrões da classe
     *
     * @return void
     */
    private function unsetCustomAttributes()
    {
        $instance   = self::instance();
        $attributes = $instance->getAttributes();

        foreach ($attributes as $attribute) {
            if ($instance->isNotDefaultAttribute($attribute)) {
                unset($instance->$attribute);
            }

        }
    }

    /**
     * Seta mensagem
     *
     * @param string $message
     * @return void
     */
    public static function message($message = '')
    {
        $instance = self::instance();

        $instance->setMessage($message);
    }

    /**
     * Seta atributos na classe para uso externo.
     *
     * @param array Array associativo de informações
     * @return \TResponse\TResponse
     */
    public static function info(array $info)
    {
        $instance = self::instance();

        $instance->setAttributes($info);

        return $instance;
    }
    public static function setData(array $info)
    {
        return self::info($info);
    }

    /**
     * @return array Array dos atributos definidos na classe
     */
    private function getAttributes()
    {
        $attributes = get_object_vars($this);

        $keys = [];
        foreach ($attributes as $key => $value) {
            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * Seta os atributos recebidos na classe, caso a chave se repita,
     * um afixo '_info' é inserido.
     *
     * @param array Array associativo de atributos para setar na classe
     * @return null
     */
    private function setAttributes(array $array)
    {
        $existing = $this->getAttributes();

        foreach ($array as $key => $value) {
            if (!in_array($key, $existing)) {
                $this->$key = $value;
            } else {
                $new_key        = "{$key}_info";
                $this->$new_key = $value;
            }
        }
    }

    /**
     * Seta as mensagens na classe
     *
     * @param string
     * @return void
     */
    private function setMessage(string $message = '')
    {
        if (empty($message)) {
            $this->message = $this->getDefaultMessage();
        } else {
            $this->message = $message;
        }
    }

    /**
     * Retorna a mensagem de erro ou de sucesso padrão
     *
     * @return string
     */
    private function getDefaultMessage()
    {
        return $this->getError() ?
        self::DEFAULT_ERROR_MESSAGE :
        self::DEFAULT_SUCCESS_MESSAGE;
    }

    /**
     * Recebe o argumento e verifica se é uma instância de Throwable
     * Caso seja, muda o status para 'danger' (vermelho) e se o APP_ENV for local
     * adiciona as informações(arquivo, linha, mensagem, trace) do Throwable na classe.
     *
     * @param string|\Throwable
     */
    private static function isException($arg)
    {
        return ($arg instanceof Throwable);
    }

    /**
     * Adiciona as informações da exception na classe
     *
     * @param Throwable $exception
     * @return void
     */
    private function setException(Throwable $exception, $message = '')
    {
        $instance = self::instance();

        $instance->setError(true);

        if (!is_int($exception->getCode())) {
            $instance->setStatus(500);
        } else {
            $instance->setStatus($exception->getCode());

        }

        if (true) {

            $instance->setMessage($exception->getMessage());

            $instance->setAttributes([
                'type'  => get_class($exception),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);

        } else {

            $instance->setMessage(self::DEFAULT_EXCEPTION_MESSAGE);

        }
    }

    /**
     * Identifica se o $subject é uma instância de TResponse
     * Se é, retorna se ocorreu erro; Caso contrário, retorna falso
     *
     * @param mixed $subject - Qualquer coisa
     * @return boolean
     */
    public static function detectError($subject)
    {
        if ($subject instanceof self) {
            return $subject->getError();
        }

        return false;
    }

    /**
     * Retorna somente $error e alguma informação adicional
     *
     * @return \TResponse\TResponse
     */
    public static function api()
    {
        $instance = self::instance();

        if (!$instance->getError()) {
            $instance->unsetMessage();
        }

        return json_encode($instance);
    }

    /**
     * Limpa o atributo messages, caso vazio
     *
     * @return void
     */
    private function unsetMessage()
    {
        if (
            empty($this->message) ||
            ($this->message == self::DEFAULT_SUCCESS_MESSAGE) ||
            ($this->message == self::DEFAULT_ERROR_MESSAGE) ||
            ($this->message == self::DEFAULT_EXCEPTION_MESSAGE)
        ) {
            unset($this->message);
        }
    }

    /**
     * Limpa o atributo status
     *
     * @return void
     */
    private function unsetStatus()
    {
        unset($this->status);
    }

    public static function hasError()
    {
        $instance = self::instance();
        return $instance->getError();
    }

    /**
     * Retorna se o nome do atributo é um atributo padrão da classe
     *
     * @return bool
     */
    private function isNotDefaultAttribute($attribute)
    {
        return (!in_array($attribute, self::DEFAULT_ATTRIBUTES));
    }

    /**
     * Checa se está no ambiente laravel
     *
     * @return bool
     */
    private static function localEnv()
    {
        return (
            function_exists('env') &&
            (env('APP_ENV') == 'local')
        );
    }

}
