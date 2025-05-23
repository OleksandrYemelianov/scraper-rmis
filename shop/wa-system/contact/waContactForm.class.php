<?php

/**
 * Builder of forms containing contact fields.
 */
class waContactForm
{
    /** @var waContactField[] field_id => waContactField */
    public $fields;

    /** @var array */
    public $options;

    /** @var array field_id => list of error message strings */
    public $errors = array();

    /** @var array field_id => value to show in field, as accepted by waContactField->getHTML(). Existing POST data overwrite this. */
    public $values = array();

    /** @var boolean Used by validateFields() to validate at most once. */
    protected $fields_validated = false;

    /** Contact to validate this form against. */
    public $contact = null;

    /** Can be used to feed faked POST data into this form. */
    public $post = null;

    /**
     * Factory method to load form fields from config.
     *
     * Config must return an array: field_id => waContactField OR array of options to specify on existing field with given field_id.
     *
     * @param string|array $file path to config file, or array of config options.
     * @param array $options
     * @return self
     * @throws waException
     */
    public static function loadConfig($file, $options = array())
    {
        $config = self::readConfig($file);
        $form = new self($config['fields'], $options);
        $form->setValue($config['values']);
        return $form;
    }

    protected static function readConfig($file)
    {
        if (is_scalar($file)) {
            if (waConfig::get('is_template')) {
                throw new waException('waContactForm::readConfig() is not allowed in template context');
            }

            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                throw new waException('waContactForm::readConfig() allows reading only php configs');
            }
        }

        if (is_array($file)) {
            $fields_config = $file;
        } else {
            if (!is_readable($file)) {
                throw new waException('Config is not readable: '.$file);
            }
            $fields_config = include($file);
            if (!$fields_config || !is_array($fields_config)) {
                waLog::log('Incorrect config '.$file);
                $fields_config = array();
            }
        }

        $fields = array();
        $values = array(); // hidden field values known beforehand
        foreach ($fields_config as $full_field_id => $opts) {
            if ($opts instanceof waContactField) {
                $f = clone $opts;
            } elseif (is_array($opts)) {
                // Allow to specify something like 'phone.home' as field_id in config file.
                $fid = explode('.', $full_field_id, 2);
                $fid = $fid[0];

                $f = waContactFields::get($fid);
                if (!$f) {
                    waLog::log('ContactField '.$fid.' not found.');
                    continue;
                } else {
                    // Prepare fields parameter for composite field
                    if ($f instanceof waContactCompositeField && !empty($opts['fields'])) {
                        if (!is_array($opts['fields'])) {
                            unset($opts['fields']);
                        } else {
                            $old_subfields = $f->getFields();
                            $subfields = array();
                            foreach ($opts['fields'] as $sfid => $sfopts) {
                                if (empty($old_subfields[$sfid])) {
                                    waLog::log('Field '.$fid.':'.$sfid.' not found and is ignored in '.(is_array($file) ? 'config' : $file));
                                    continue;
                                }
                                $subfields[$sfid] = self::getClone($old_subfields[$sfid], $sfopts);
                                if ($subfields[$sfid] instanceof waContactHiddenField) {
                                    if (empty($values[$full_field_id]['data'])) {
                                        $values[$full_field_id] = array('data' => array());
                                    }
                                    $values[$full_field_id]['data'][$sfid] = $subfields[$sfid]->getParameter('value');
                                }
                            }

                            $opts['fields'] = $subfields;
                        }
                    }

                    $f = self::getClone($f, $opts);
                    if ($f instanceof waContactHiddenField) {
                        $values[$full_field_id] = $f->getParameter('value');
                    }
                }
            } else {
                waLog::log('Field '.$full_field_id.' has incorrect format and is ignored in '.$file);
                continue;
            }

            $fields[$full_field_id] = $f;
        }
        return array(
            'fields' => $fields,
            'values' => $values
        );
    }

    /**
     * @param waContactField $f
     * @param $opts
     * @return waContactField
     */
    protected static function getClone($f, $opts)
    {
        if (!is_array($opts)) {
            return clone $f;
        }

        if (!empty($opts['hidden'])) {
            return new waContactHiddenField($f->getId(), $f->getName(), $opts);
        }

        $f = clone $f;
        foreach ($opts as $k => $v) {
            $f->setParameter($k, $v);
        }
        return $f;
    }

    /**
     * Options:
     * - namespace
     *   - Prefix for all fields of this form.
     *   - Defaults to 'data'.
     *
     * @param array $fields list of waContactField
     * @param array $options
     * @throws waException
     */
    public function __construct($fields = array(), $options = array())
    {
        if (!is_array($fields)) {
            throw new waException('$fields must be an array');
        }
        $this->fields = array();
        foreach ($fields as $full_field_id => $f) {
            if (!($f instanceof waContactField)) {
                throw new waException('Bad parameters for '.get_class($this));
            }

            // Allows to specify a list of fields instead of key => value pairs
            if (!$full_field_id || is_numeric($full_field_id)) {
                $full_field_id = $f->getId();
            }

            $this->fields[$full_field_id] = $f;
        }

        if (!is_array($options)) {
            throw new waException('$options must be an array');
        }
        $this->options = $options;
        $this->options['namespace'] = ifempty($this->options['namespace'], 'data');
    }

    /**
     * Set form value.
     * Note that when POST exists, data from POST overwrite setValue().
     * Accepts 1 or 2 parameters.
     * - 1 parameter: array field_id => value. Set several values.
     * - 1 parameter: waContact. Fetch data via waContact->load()
     * - 2 parameters: field_id, value. Set value for single field.
     */
    public function setValue($field_id, $value = null)
    {
        if (func_num_args() == 1) {
            if ($field_id instanceof waContact) {
                $c = $field_id;
                $arr = array();
                foreach ($this->fields as $fid => $f) {
                    if ($fid == 'name' && $c['is_user']) {
                        // Contact name parser-backend always accepts name parts as set up for Contacts, not Users
                        $arr[$fid] = waContactNameField::formatName($c, true);
                    } else {
                        $arr[$fid] = $c->get($fid);
                    }
                }
            } elseif (is_array($field_id)) {
                $arr = $field_id;
            } else {
                return $this;
            }
        } else {
            $arr = array($field_id => $value);
        }

        foreach ($arr as $fid => $v) {
            if (isset($this->fields[$fid])) {
                $this->values[$fid] = $v;
            }
        }

        return $this;
    }

    /**
     * @param string $field_id
     * @return mixed POST data for entire form or single form field; null when no POST submitted.
     */
    public function post($field_id = null)
    {
        if ($this->post === null && waRequest::post($this->opt('namespace'))) {
            $post = array();
            $fields = $this->fields();
            foreach ((array)waRequest::post($this->opt('namespace')) as $f_id => $value) {
                $value = $this->preparePostValue($value);
                if (isset($fields[$f_id])) {
                    $post[$f_id] = $value;
                }
            }
            if ($post) {
                $this->post = $post;
            }
        }
        if ($this->post === null || !is_array($this->post)) {
            return null;
        }
        if ($field_id) {
            if (!isset($this->post[$field_id])) {
                if (isset($this->values[$field_id])) {
                    return $this->values[$field_id];
                } else {
                    return null;
                }
            } else {
                return $this->post[$field_id];
            }

        }
        return $this->post;
    }

    /**
     * Cuts off spaces at all possible values.
     * @param $values
     * @return array|string
     */
    protected function preparePostValue($values)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (is_string($value)) {
                    $values[$key] = trim($value);
                }
            }
        } elseif (is_string($values)) {
            $values = trim($values);
        }

        return $values;
    }

    /**
     * Get list of errors for specified field, or append an error to the list.
     *
     * With no parameters returns an array of all errors: field_id => list of strings.
     *
     * With one parameter returns a list of errors for one field.
     *
     * With two parameters appends an error to the list of errors for specified field.
     * This sets internal state so that form HTML will contain given error message next to the field.
     * Forces isValid() to return false.
     *
     * @param string $field_id field_id or null to set message for entire form, not attached to any field.
     * @param string $error_text
     * @return array|mixed|waContactForm|null
     */
    public function errors($field_id = '', $error_text = null)
    {
        if (func_num_args() === 0) {
            return $this->errors;
        }
        if ($field_id === null || empty($this->fields[$field_id])) {
            $field_id = '';
        }
        if (strlen($error_text) <= 0) {
            $this->validateFields();
            return ifset($this->errors[$field_id], array());
        }
        if (empty($this->errors[$field_id])) {
            $this->errors[$field_id] = array();
        }
        $this->errors[$field_id][] = $error_text;

        $this->treatNamesFieldValidation();

        return $this;
    }

    /**
     * Validate this form and set internal state so that form HTML will contain error messages.
     * @param waContact $contact
     * @return boolean true when no errors encountered; otherwise false.
     */
    public function isValid($contact = null)
    {
        $this->validateFields($contact);
        $this->treatNamesFieldValidation();
        return !$this->errors;
    }

    /**
     * Get specified form field or all of them.
     * @param string $field_id
     * @return waContactField|waContactField[]
     */
    public function fields($field_id = null)
    {
        if ($field_id) {
            if (isset($this->fields[$field_id])) {
                return $this->fields[$field_id];
            }
            return null;
        }
        return $this->fields;
    }

    /**
     * HTML for the whole form or single form field.
     * @param string $field_id
     * @param boolean $with_errors whether to add class="error" and error text next to form fields
     * @param bool $placeholders
     * @return string HTML
     */
    public function html($field_id = null, $with_errors = true, $placeholders = false)
    {
        $this->validateFields();
        $this->treatNamesFieldValidation();

        // Single field?
        if ($field_id) {
            if (empty($this->fields[$field_id])) {
                return '';
            }

            $opts = $this->options;
            $opts['id'] = $field_id;
            $opts['my_profile'] = $this->fields[$field_id]->getParameter('my_profile');

            if (empty($this->contact)) {
                $this->contact = new waContact();
            }
            if ($this->post() !== null) {
                $opts['value'] = $this->fields[$field_id]->set($this->contact, $this->post($field_id), array());
            } elseif (isset($this->values[$field_id]) &&
                ((is_array($this->values[$field_id]) && count($this->values[$field_id]) > 0) ||
                    (!is_array($this->values[$field_id]) && strlen((string)$this->values[$field_id])))) {
                $opts['value'] = $this->fields[$field_id]->set($this->contact, $this->values[$field_id], array());
            } else {
                $default_value = $this->fields[$field_id]->getParameter('value');
                if ($default_value) {
                    $opts['value'] = $this->fields[$field_id]->set($this->contact, $default_value, array());
                }
            }

            // HTML with no errors?
            if ($with_errors && !empty($this->errors[$field_id]) && !empty($this->errors[$field_id])) {
                $opts['validation_errors'] = $this->errors[$field_id];
            }

            // output password field with 'confirm password' field like composite fields
            if ($field_id === 'password') {
                $this->fields[$field_id]->setParameter('localized_names', _ws('New password'));
                $opts['add_password_confirm'] = true;
            }
            if ($placeholders) {
                $opts['placeholder'] = true;
            }
            return $this->fields[$field_id]->getHTML($opts);
        }

        // Whole form
        $class_field = $this->opt('css_class_field', wa()->getEnv() == 'frontend' ? 'wa-field' : 'field');
        $class_value = $this->opt('css_class_value', wa()->getEnv() == 'frontend' ? 'wa-value' : 'value');
        $class_name = $this->opt('css_class_name', wa()->getEnv() == 'frontend' ? 'wa-name' : 'name');
        $result = '';
        foreach ($this->fields() as $fid => $f) {
            /** @var waContactField $f */

            // Upload contact photo
            if ($fid === 'photo') {
                $result .= '<div class="'.$class_field.' '.($class_field.'-'.$f->getId()).'"><div class="'.$class_name.'">'.
                    _ws('Photo').'</div><div class="'.$class_value.'">';

                // Current photo of a person
                if (wa()->getUser()->get($fid)) {
                    $result .= "\n".'<img src="'.wa()->getUser()->getPhoto().'">';
                }

                // Empty photo
                $result .= "\n".'<img src="'.waContact::getPhotoUrl(null, null, null, null, 'person').'">';

                $result .= "\n".'<p><input type="file" name="'.$fid.'_file"></p>';
                $result .= $this->html($fid, true);
                $result .= "\n</div></div>";
                continue;
            }

            // Fake password confirmation field
            if ($fid === 'password_confirm') {
                continue;
            }

            // Hidden field
            if ($f->isHidden()) {
                $result .= $this->html($fid, true);
                continue;
            }

            $field_class = $class_field.'-'.$f->getId();
            if (strpos($fid, '.') !== false) {
                $field_class .= ' '.$class_field.'-'.str_replace('.', '-', $fid);
            }
            if ($f->isRequired()) {
                $field_class .= ' '.(wa()->getEnv() == 'frontend' ? 'wa-required' : 'required');
            }
            $result .= '<div class="'.$class_field.' '.$field_class.'"><div class="'.$class_name.'">'.
                $f->getName(null, true).'</div><div class="'.$class_value.'">';
            $result .= "\n".$this->html($fid, $with_errors, $placeholders);
            $result .= "\n</div></div>";
        }
        $result .= '<input type="hidden" name="_csrf" value="'.waRequest::cookie('_csrf', '').'" />';

        return $result;
    }

    /**
     * Value of a single option, or the whole options array.
     *
     * @param string $name
     * @param mixed $default value to return when no option with this $name specified
     * @return mixed
     */
    public function opt($name = null, $default = null)
    {
        if ($name === null) {
            return $this->options;
        }
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        return $default;
    }

    /**
     * Make sure POST data is properly validated using waContactField instances in $this->fields.
     * @param waContact $contact
     */
    protected function validateFields($contact = null)
    {
        if (!$contact || !($contact instanceof waContact)) {
            $contact = null;
        }
        if ($this->fields_validated && (!$contact || $contact === $this->contact)) {
            return;
        }
        $this->contact = $contact ? $contact : new waContact();
        $this->fields_validated = true;
        if ($this->post() === null) {
            return;
        }

        foreach ($this->fields as $fid => $f) {
            $errors = $this->validateField($f, $fid, $this->contact);
            if (!$errors) {
                continue;
            }
            if (!is_array($errors)) {
                $errors = array($errors);
            }
            if (empty($this->errors[$fid])) {
                $this->errors[$fid] = array();
            }
            if (empty($this->errors[$fid])) {
                $this->errors[$fid] = $errors;
            } else {
                $this->errors[$fid] = array_merge($this->errors[$fid], $errors);
            }
        }
    }

    /**
     * @param waContactField $field
     * @param string $field_id
     * @param waContact $contact
     * @return mixed
     */
    protected function validateField($field, $field_id, $contact)
    {
        return $field->validate($field->set($contact, $this->post($field_id), array()), $contact->getId());
    }

    /**
     * System waContactNameField field always requires "At least one of these fields must be filled in."
     * In this situation other name fields ('firstname', 'middlename', 'lastname') need to be marked visually (by error class)
     * To achieve it call this method
     *
     * Must be called after validateFields and after each call of $this->errors()
     */
    protected function treatNamesFieldValidation()
    {
        if (isset($this->errors['name']) && $this->fields('name')) {
            $name_fields = array('firstname', 'middlename', 'lastname');
            foreach ($name_fields as $name_field) {
                if ($this->fields($name_field) && empty($this->errors[$name_field])) {
                    $this->errors[$name_field] = array(
                        '' // just mark field red
                    );
                }
            }
        }
    }
}
