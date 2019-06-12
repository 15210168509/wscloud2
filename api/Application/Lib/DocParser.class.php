<?php
namespace Lib;
    /**
     * 权限认证类
     * 功能特性：
     * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
     *      $auth=new Auth();  $auth->check('规则名称','用户id')
     * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
     *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
     *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
     * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
     *
     * 4，支持规则表达式。
     *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
     */

class DocParser{

    private $params = array ();
    public function __construct()
    {

    }

    public function parse($doc = '') {
        if ($doc == '') {
            return $this->params;
        }
        // Get the comment
        if (preg_match ( '#^/\*\*(.*)\*/#s', $doc, $comment ) === false)
            return $this->params;
        $comment = trim ( $comment [1] );
        // Get all the lines and strip the * from the first character
        if (preg_match_all ( '#^\s*\*(.*)#m', $comment, $lines ) === false)
            return $this->params;
        $this->parseLines ( $lines [1] );
        return $this->params;
    }
    private function parseLines($lines) {
        foreach ( $lines as $line ) {
            $parsedLine = $this->parseLine ( $line ); // Parse the line

            if ($parsedLine === false && ! isset ( $this->params ['description'] )) {
                if (isset ( $desc )) {
                    // Store the first line in the short description
                    $this->params ['description'] = implode ( PHP_EOL, $desc );
                }
                $desc = array ();
            } elseif ($parsedLine !== false) {
                $desc [] = $parsedLine; // Store the line in the long description
            }
        }
        $desc = implode ( ' ', $desc );
        if (! empty ( $desc ))
            $this->params ['long_description'] = $desc;
    }
    private function parseLine($line) {
        // trim the whitespace from the line
        $line = trim ( $line );

        if (empty ( $line ))
            return false; // Empty line

        if (strpos ( $line, '@' ) === 0) {
            if (strpos ( $line, ' ' ) > 0) {
                // Get the parameter name
                $param = substr ( $line, 1, strpos ( $line, ' ' ) - 1 );
                $value = substr ( $line, strlen ( $param ) + 2 ); // Get the value
            } else {
                $param = substr ( $line, 1 );
                $value = '';
            }
            // Parse the line and return false if the parameter is valid
            if ($this->setParam ( $param, $value ))
                return false;
        }

        return $line;
    }
    private function setParam($param, $value) {
        if ($param == 'param' || $param == 'return')
            $value = $this->formatParamOrReturn ( $value );
        if ($param == 'class')
            list ( $param, $value ) = $this->formatClass ( $value );

        if (empty ( $this->params [$param] )) {
            $this->params [$param] = $value;
        } else if ($param == 'param') {
            $arr = array (
                $this->params [$param],
                $value
            );
            $this->params [$param] = $arr;
        } else {
            $this->params [$param] = $value + $this->params [$param];
        }
        return true;
    }
    private function formatClass($value) {
        $r = preg_split ( "[|]", $value );
        if (is_array ( $r )) {
            $param = $r [0];
            parse_str ( $r [1], $value );
            foreach ( $value as $key => $val ) {
                $val = explode ( ',', $val );
                if (count ( $val ) > 1)
                    $value [$key] = $val;
            }
        } else {
            $param = 'Unknown';
        }
        return array (
            $param,
            $value
        );
    }
    private function formatParamOrReturn($string) {
        $pos = strpos ( $string, ' ' );

        $type = substr ( $string, 0, $pos );
        return '(' . $type . ')' . substr ( $string, $pos + 1 );
    }

}
