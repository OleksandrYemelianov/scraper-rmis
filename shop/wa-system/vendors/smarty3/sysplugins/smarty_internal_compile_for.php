<?php
/**
 * Smarty Internal Plugin Compile For
 *
 * Compiles the {for} {forelse} {/for} tags
 *
 * @package Smarty
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile For Class
 *
 * @package Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_For extends Smarty_Internal_CompileBase {

    /**
     * Compiles code for the {for} tag
     *
     * Smarty 3 does implement two different sytaxes:
     *
     * - {for $var in $array}
     * For looping over arrays or iterators
     *
     * - {for $x=0; $x<$y; $x++}
     * For general loops
     *
     * The parser-backend is gereration different sets of attribute by which this compiler can
     * determin which syntax is used.
     *
     * @param array  $args      array with attributes from parser-backend
     * @param object $compiler  compiler object
     * @param array  $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        if ($parameter == 0) {
            $this->required_attributes = array('start', 'to');
            $this->optional_attributes = array('max', 'step');
        } else {
            $this->required_attributes = array('start', 'ifexp', 'var', 'step');
            $this->optional_attributes = array();
        }
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $output = "<?php ";
        if ($parameter == 1) {
            foreach ($_attr['start'] as $_statement) {
                $output .= " \$_smarty_tpl->tpl_vars[$_statement[var]] = new Smarty_Variable;";
                $output .= " \$_smarty_tpl->tpl_vars[$_statement[var]]->value = $_statement[value];\n";
            }
            $output .= "  if ($_attr[ifexp]){ for (\$_foo=true;$_attr[ifexp]; \$_smarty_tpl->tpl_vars[$_attr[var]]->value$_attr[step]){\n";
        } else {
            $_statement = $_attr['start'];
            $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]] = new Smarty_Variable;";
            if (isset($_attr['step'])) {
                $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->step = $_attr[step];";
            } else {
                $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->step = 1;";
            }
            if (isset($_attr['max'])) {
                $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->total = (int)min(ceil((\$_smarty_tpl->tpl_vars[$_statement[var]]->step > 0 ? $_attr[to]+1 - ($_statement[value]) : $_statement[value]-($_attr[to])+1)/abs(\$_smarty_tpl->tpl_vars[$_statement[var]]->step)),$_attr[max]);\n";
            } else {
                $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->total = (int)ceil((\$_smarty_tpl->tpl_vars[$_statement[var]]->step > 0 ? $_attr[to]+1 - ($_statement[value]) : $_statement[value]-($_attr[to])+1)/abs(\$_smarty_tpl->tpl_vars[$_statement[var]]->step));\n";
            }
            $output .= "if (\$_smarty_tpl->tpl_vars[$_statement[var]]->total > 0){\n";
            $output .= "for (\$_smarty_tpl->tpl_vars[$_statement[var]]->value = $_statement[value], \$_smarty_tpl->tpl_vars[$_statement[var]]->iteration = 1;\$_smarty_tpl->tpl_vars[$_statement[var]]->iteration <= \$_smarty_tpl->tpl_vars[$_statement[var]]->total;\$_smarty_tpl->tpl_vars[$_statement[var]]->value += \$_smarty_tpl->tpl_vars[$_statement[var]]->step, \$_smarty_tpl->tpl_vars[$_statement[var]]->iteration++){\n";
            $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->first = \$_smarty_tpl->tpl_vars[$_statement[var]]->iteration == 1;";
            $output .= "\$_smarty_tpl->tpl_vars[$_statement[var]]->last = \$_smarty_tpl->tpl_vars[$_statement[var]]->iteration == \$_smarty_tpl->tpl_vars[$_statement[var]]->total;";
        }
        $output .= "?>";

        $this->openTag($compiler, 'for', array('for', $compiler->nocache));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        // return compiled code
        return $output;
    }

}

/**
 * Smarty Internal Plugin Compile Forelse Class
 *
 * @package Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Forelse extends Smarty_Internal_CompileBase {

    /**
     * Compiles code for the {forelse} tag
     *
     * @param array  $args      array with attributes from parser-backend
     * @param object $compiler  compiler object
     * @param array  $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr  = $this->getAttributes($compiler, $args);

        list($openTag, $nocache) = $this->closeTag($compiler, array('for'));
        $this->openTag($compiler, 'forelse', array('forelse', $nocache));
        return "<?php }} else { ?>";
    }

}

/**
 * Smarty Internal Plugin Compile Forclose Class
 *
 * @package Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Forclose extends Smarty_Internal_CompileBase {

    /**
     * Compiles code for the {/for} tag
     *
     * @param array  $args      array with attributes from parser-backend
     * @param object $compiler  compiler object
     * @param array  $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache) = $this->closeTag($compiler, array('for', 'forelse'));

        if ($openTag == 'forelse') {
            return "<?php }  ?>";
        } else {
            return "<?php }} ?>";
        }
    }

}

?>