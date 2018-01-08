<?php

namespace App\Core\Syntax\Token;
use JsonSerializable;

class Token implements JsonSerializable
{
    private $type;
    private $line;
    private $column;
    private $isEOF = false;
    private $text;

    public function __construct(TokenType $type = null)
    {
        $this->type = $type;
    }

    public static function eof()
    {
        $token = new Token();
        $token->text = '<EOF>';
        $token->isEOF = true;
        $token->type = TokenType::ws(); //JavaTokenType.WS;
        return $token;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(TokenType $type)
    {
        $this->type = $type;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setColumn(int $column)
    {
        $this->column = $column;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine(int $line)
    {
        $this->line = $line;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function isEOF()
    {
        return $this->isEOF;
    }

    public function jsonSerialize()
    {
        return [
            'type'   => $this->type,
            'line'   => $this->line,
            'column' => $this->column,
            'isEOF'  => $this->isEOF,
            'text'   => $this->text
        ];
    }

    public function __toString()
    {
        $txt = $this->text;

        if (isset($txt)) {
            $txt = str_replace('\n', '\\n', $txt);
            $txt = str_replace('\r', '\\r', $txt);
            $txt = str_replace('\t', '\\t', $txt);
        } else {
            $txt = '<no text>';
        }

        $typeString = strval($type);

        return "['$txt', <$typeString> $this->line:$this->column]";
    }
}
