<?php

/**
 * A validation class
 * 
 * @author wuxiao
 * @since 2015-4-7
 */
class Validator{
        
        private static $_instance;
        
        private static $_validate;
        
        private static $_message;
        
        /**
	 * The data under validation.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The files under validation.
	 *
	 * @var array
	 */
	protected $files = array();

	/**
	 * The rules to be applied to the data.
	 *
	 * @var array
	 */
	protected $rules;
        
        protected $verifier = array(
            'accepted','active_url','after','alpha','alpha_dash','alpha_num','array',
            'before','between','confirmed','date','date_format','different','digits',
            'digits_between','boolean','email','image','in','integer','ip','max','mimes',
            'min','not_in','numeric','regex','required','required_if','required_with',
            'required_with_all','required_without','required_without_all','same',
            'size','timezone','url',
            //TODO 'exists','unique'
        );
        
        /**
	 * The array of custom error messages.
	 *
	 * @var array
	 */
	protected $customMessages = array();
        
        protected $messages = array();
        
        private static function factory(){
                if (!self::$_instance instanceof Validator){
                        self::$_instance = new self;
                        self::$_validate = new Validate;
                        self::$_message = new Message;
                }
                return self::$_instance;
        }
        
        public static function make(array $rules, array $data = array()){
                $validator = self::factory();
                $validator->parseData($data);
                $validator->resolve($rules);
                //echo '<pre>';var_export($validator->rules);echo '</pre>';
                return $validator;
        }
        
        protected function parseData(array $data){
                if (!empty($data)){
                        $this->data = $data;
                }else{
                        $this->data = $_REQUEST;
                }
                
                if (!empty($_FILES)){
                        foreach ($_FILES as $attribute=>$file){
                               $file = new SplFileInfo($file['tmp_name']);
                               $this->files[$attribute] = $file;
                        }
                }
                
                self::$_validate->data = $this->data;
                self::$_message->data = $this->data;
        }
        
        protected function resolve(array $rules){
                foreach ($rules as $attribute=>&$ruleList){
                        $ruleList = (is_string($ruleList)) ? explode('|', $ruleList) : $ruleList;
                        foreach ($ruleList as &$rule){
                                if (strpos($rule, ':') !== false){
                                        $rule = explode(':', $rule, 2);
                                        $rule = array(snake_case($rule[0]), explode(',', $rule[1]));
                                }else{
                                        $rule = snake_case($rule);
                                }
                        }
                }
                $this->rules = $rules;
        }
        
        public function passes(){
                foreach ($this->rules as $attribute => $rules)
                {
                        foreach ($rules as $rule)
                        {
                                if (is_array($rule)){
                                        list($rule,$parameters) = $rule;
                                }else{
                                        $parameters = null;
                                }
                                $ret = $this->validate($attribute, $rule, $parameters);
                        }
                }
                
                return count($this->messages) === 0;
        }
        
        private function validate($attribute, $rule, $parameters = null){
                $rule = studly($rule);
                $method = "validate{$rule}";
                $value = $this->getValue($attribute);
                if (!self::$_validate->$method($attribute,$value,$parameters)){
                        self::$_message->addFailure($attribute, $rule, $parameters,self::$_instance);
                        return false;
                }
                return true;
        }
        
        /**
	 * Get the value of a given attribute.
	 *
	 * @param  string  $attribute
	 * @return mixed
	 */
	protected function getValue($attribute)
	{
		if ( ! is_null($value = array_get($this->data, $attribute)))
		{
			return $value;
		}
		elseif ( ! is_null($value = array_get($this->files, $attribute)))
		{
			return $value;
		}
	}
        
        public function fails(){
                return ! $this->passes();
        }
        
        public static function setMessage($customMessages){
                if (!self::$_instance instanceof Validator)
                        return false;
                foreach ($customMessages as $key => $customMessage){
                        self::$_message->customMessages[snake_case($key,'')] = $customMessage;
                }
        }
        
        public function messages(){
                return empty($this->messages) ? array() : $this->messages;
        }
        
}

class Validate extends Validator{
        
        /**
	 * Validate that a required attribute exists.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateRequired($attribute, $value)
	{
		if (is_null($value))
		{
			return false;
		}
		elseif (is_string($value) && trim($value) === '')
		{
			return false;
		}
		elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1)
		{
			return false;
		}

		return true;
	}

	/**
	 * Validate the given attribute is filled if it is present.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateFilled($attribute, $value)
	{
		if (array_key_exists($attribute, $this->data) || array_key_exists($attribute, $this->files))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Determine if any of the given attributes fail the required test.
	 *
	 * @param  array  $attributes
	 * @return bool
	 */
	protected function anyFailingRequired(array $attributes)
	{
		foreach ($attributes as $key)
		{
			if ( ! $this->validateRequired($key, $this->getValue($key)))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if all of the given attributes fail the required test.
	 *
	 * @param  array  $attributes
	 * @return bool
	 */
	protected function allFailingRequired(array $attributes)
	{
		foreach ($attributes as $key)
		{
			if ($this->validateRequired($key, $this->getValue($key)))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate that an attribute exists when any other attribute exists.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  mixed   $parameters
	 * @return bool
	 */
	protected function validateRequiredWith($attribute, $value, $parameters)
	{
		if ( ! $this->allFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Validate that an attribute exists when all other attributes exists.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  mixed   $parameters
	 * @return bool
	 */
	protected function validateRequiredWithAll($attribute, $value, $parameters)
	{
		if ( ! $this->anyFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Validate that an attribute exists when another attribute does not.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  mixed   $parameters
	 * @return bool
	 */
	protected function validateRequiredWithout($attribute, $value, $parameters)
	{
		if ($this->anyFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Validate that an attribute exists when all other attributes do not.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  mixed   $parameters
	 * @return bool
	 */
	protected function validateRequiredWithoutAll($attribute, $value, $parameters)
	{
		if ($this->allFailingRequired($parameters))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Validate that an attribute exists when another attribute has a given value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  mixed   $parameters
	 * @return bool
	 */
	protected function validateRequiredIf($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'required_if');

		$data = array_get($this->data, $parameters[0]);

		$values = array_slice($parameters, 1);

		if (in_array($data, $values))
		{
			return $this->validateRequired($attribute, $value);
		}

		return true;
	}

	/**
	 * Validate that an attribute has a matching confirmation.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateConfirmed($attribute, $value)
	{
		return $this->validateSame($attribute, $value, array($attribute.'_confirmation'));
	}

	/**
	 * Validate that two attributes match.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateSame($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'same');

		$other = array_get($this->data, $parameters[0]);

		return isset($other) && $value == $other;
	}

	/**
	 * Validate that an attribute is different from another attribute.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateDifferent($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'different');

		$other = array_get($this->data, $parameters[0]);

		return isset($other) && $value != $other;
	}

	/**
	 * Validate that an attribute was "accepted".
	 *
	 * This validation rule implies the attribute is "required".
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAccepted($attribute, $value)
	{
		$acceptable = array('yes', 'on', '1', 1, true, 'true');

		return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
	}

	/**
	 * Validate that an attribute is an array.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateArray($attribute, $value)
	{
		return is_array($value);
	}

	/**
	 * Validate that an attribute is a boolean.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateBoolean($attribute, $value)
	{
		$acceptable = array(true, false, 0, 1, '0', '1');

		return in_array($value, $acceptable, true);
	}

	/**
	 * Validate that an attribute is an integer.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateInteger($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	}

	/**
	 * Validate that an attribute is numeric.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateNumeric($attribute, $value)
	{
		return is_numeric($value);
	}

	/**
	 * Validate that an attribute is a string.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateString($attribute, $value)
	{
		return is_string($value);
	}

	/**
	 * Validate that an attribute has a given number of digits.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateDigits($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'digits');

		return $this->validateNumeric($attribute, $value)
			&& strlen((string) $value) == $parameters[0];
	}

	/**
	 * Validate that an attribute is between a given number of digits.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateDigitsBetween($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'digits_between');

		$length = strlen((string) $value);

		return $this->validateNumeric($attribute, $value)
		  && $length >= $parameters[0] && $length <= $parameters[1];
	}

	/**
	 * Validate the size of an attribute.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateSize($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'size');

		return $this->getSize($attribute, $value) == $parameters[0];
	}

	/**
	 * Validate the size of an attribute is between a set of values.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateBetween($attribute, $value, $parameters)
	{
		$this->requireParameterCount(2, $parameters, 'between');

		$size = $this->getSize($attribute, $value);

		return $size >= $parameters[0] && $size <= $parameters[1];
	}

	/**
	 * Validate the size of an attribute is greater than a minimum value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateMin($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'min');

		return $this->getSize($attribute, $value) >= $parameters[0];
	}

	/**
	 * Validate the size of an attribute is less than a maximum value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateMax($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'max');

		return $this->getSize($attribute, $value) <= $parameters[0];
	}

	/**
	 * Get the size of an attribute.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return mixed
	 */
	protected function getSize($attribute, $value)
	{
		// This method will determine if the attribute is a number, string, or file and
		// return the proper size accordingly. If it is a number, then number itself
		// is the size. If it is a file, we take kilobytes, and for a string the
		// entire length of the string will be considered the attribute size.
		if (is_numeric($value))
		{
			return array_get($this->data, $attribute);
		}
		elseif (is_array($value))
		{
			return count($value);
		}
		elseif ($value instanceof SplFileInfo)
		{
			return $value->getSize() / 1024;
		}

		return mb_strlen($value);
	}

	/**
	 * Validate an attribute is contained within a list of values.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateIn($attribute, $value, $parameters)
	{
		return in_array((string) $value, $parameters);
	}

	/**
	 * Validate an attribute is not contained within a list of values.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateNotIn($attribute, $value, $parameters)
	{
		return ! $this->validateIn($attribute, $value, $parameters);
	}

	/**
	 * Validate the uniqueness of an attribute value on a given database table.
	 *
	 * If a database column is not specified, the attribute will be used.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateUnique($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'unique');

		$table = $parameters[0];

		// The second parameter position holds the name of the column that needs to
		// be verified as unique. If this parameter isn't specified we will just
		// assume that this column to be verified shares the attribute's name.
		$column = isset($parameters[1]) ? $parameters[1] : $attribute;

		list($idColumn, $id) = array(null, null);

		if (isset($parameters[2]))
		{
			list($idColumn, $id) = $this->getUniqueIds($parameters);

			if (strtolower($id) == 'null') $id = null;
		}

		// The presence verifier is responsible for counting rows within this store
		// mechanism which might be a relational database or any other permanent
		// data store like Redis, etc. We will use it to determine uniqueness.
		$verifier = $this->getPresenceVerifier();

		$extra = $this->getUniqueExtra($parameters);

		return $verifier->getCount(

			$table, $column, $value, $id, $idColumn, $extra

		) == 0;
	}

	/**
	 * Get the excluded ID column and value for the unique rule.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getUniqueIds($parameters)
	{
		$idColumn = isset($parameters[3]) ? $parameters[3] : 'id';

		return array($idColumn, $parameters[2]);
	}

	/**
	 * Get the extra conditions for a unique rule.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getUniqueExtra($parameters)
	{
		if (isset($parameters[4]))
		{
			return $this->getExtraConditions(array_slice($parameters, 4));
		}

		return array();
	}

	/**
	 * Validate the existence of an attribute value in a database table.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateExists($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'exists');

		$table = $parameters[0];

		// The second parameter position holds the name of the column that should be
		// verified as existing. If this parameter is not specified we will guess
		// that the columns being "verified" shares the given attribute's name.
		$column = isset($parameters[1]) ? $parameters[1] : $attribute;

		$expected = (is_array($value)) ? count($value) : 1;

		return $this->getExistCount($table, $column, $value, $parameters) >= $expected;
	}
        
        /**
	 * Validate that an attribute is a valid IP.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateIp($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * Validate that an attribute is a valid e-mail address.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateEmail($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Validate that an attribute is a valid URL.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateUrl($attribute, $value)
	{
		return filter_var($value, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * Validate that an attribute is an active URL.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateActiveUrl($attribute, $value)
	{
		$url = str_replace(array('http://', 'https://', 'ftp://'), '', strtolower($value));

		return checkdnsrr($url, 'A');
	}

	/**
	 * Validate the MIME type of a file is an image MIME type.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateImage($attribute, $value)
	{
		return $this->validateMimes($attribute, $value, array('jpeg', 'png', 'gif', 'bmp', 'svg','jpg'));
	}

	/**
	 * Validate the MIME type of a file upload attribute is in a set of MIME types.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateMimes($attribute, $value, $parameters)
	{
		if ( ! $value instanceof SplFileInfo)
		{
			return false;
		}

		return $value->getPath() != '' && in_array(fileext($value->getRealPath(),true), $parameters);
	}
        
	/**
	 * Validate that an attribute contains only alphabetic characters.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlpha($attribute, $value)
	{
		return preg_match('/^[\pL\pM]+$/u', $value);
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlphaNum($attribute, $value)
	{
		return preg_match('/^[\pL\pM\pN]+$/u', $value);
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateAlphaDash($attribute, $value)
	{
		return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
	}

	/**
	 * Validate that an attribute passes a regular expression check.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateRegex($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'regex');

		return preg_match($parameters[0], $value);
	}

	/**
	 * Validate that an attribute is a valid date.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateDate($attribute, $value)
	{
		if ($value instanceof DateTime) return true;

		if (strtotime($value) === false) return false;

		$date = date_parse($value);

		return checkdate($date['month'], $date['day'], $date['year']);
	}

	/**
	 * Validate that an attribute matches a date format.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateDateFormat($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'date_format');

		$parsed = date_parse_from_format($parameters[0], $value);

		return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
	}

	/**
	 * Validate the date is before a given date.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateBefore($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'before');

		if ( ! ($date = strtotime($parameters[0])))
		{
			return strtotime($value) < strtotime($this->getValue($parameters[0]));
		}

		return strtotime($value) < $date;
	}

	/**
	 * Validate the date is before a given date with a given format.
	 *
	 * @param  string  $format
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateBeforeWithFormat($format, $value, $parameters)
	{
		$param = $this->getValue($parameters[0]) ?: $parameters[0];

		return $this->checkDateTimeOrder($format, $value, $param);
	}

	/**
	 * Validate the date is after a given date.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateAfter($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'after');

		if ( ! ($date = strtotime($parameters[0])))
		{
			return strtotime($value) > strtotime($this->getValue($parameters[0]));
		}

		return strtotime($value) > $date;
	}

	/**
	 * Validate the date is after a given date with a given format.
	 *
	 * @param  string  $format
	 * @param  mixed   $value
	 * @param  array   $parameters
	 * @return bool
	 */
	protected function validateAfterWithFormat($format, $value, $parameters)
	{
		$param = $this->getValue($parameters[0]) ?: $parameters[0];

		return $this->checkDateTimeOrder($format, $param, $value);
	}

	/**
	 * Given two date/time strings, check that one is after the other.
	 *
	 * @param  string  $format
	 * @param  string  $before
	 * @param  string  $after
	 * @return bool
	 */
	protected function checkDateTimeOrder($format, $before, $after)
	{
		$before = $this->getDateTimeWithOptionalFormat($format, $before);

		$after = $this->getDateTimeWithOptionalFormat($format, $after);

		return ($before && $after) && ($after > $before);
	}

	/**
	 * Get a DateTime instance from a string.
	 *
	 * @param  string  $format
	 * @param  string  $value
	 * @return \DateTime|null
	 */
	protected function getDateTimeWithOptionalFormat($format, $value)
	{
		$date = DateTime::createFromFormat($format, $value);

		if ($date) return $date;

		try
		{
			return new DateTime($value);
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Validate that an attribute is a valid timezone.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return bool
	 */
	protected function validateTimezone($attribute, $value)
	{
		try
		{
			new DateTimeZone($value);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
        
        /**
	 * Require a certain number of parameters to be present.
	 *
	 * @param  int    $count
	 * @param  array  $parameters
	 * @param  string  $rule
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	protected function requireParameterCount($count, $parameters, $rule)
	{
		if (count($parameters) < $count)
		{
			throw new Exception("Validation rule $rule requires at least $count parameters.");
		}
	}
}

class Message extends Validator{
        
        /**
	 * The failed validation rules.
	 *
	 * @var array
	 */
	protected $failedRules = array();
        
         /**
	 * Add a failed rule and error message to the collection.
	 *
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return void
	 */
	protected function addFailure($attribute, $rule, $parameters, Validator &$validator)
	{
		$message = $this->addError($attribute, $rule, $parameters, $validator);

		$this->failedRules[$attribute][$rule] = $parameters;
                
                $validator->messages[] = $message;
	}
        
        /**
	 * Add an error message to the validator's collection of messages.
	 *
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return void
	 */
	protected function addError($attribute, $rule, $parameters, $validator)
	{
		$message = $this->getMessage($attribute, $rule);

		$message = $this->doReplacements($message, $attribute, $rule, $parameters);

                return $message;
	}
        
        /**
	 * Get the validation message for an attribute and rule.
	 *
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @return string
	 */
	protected function getMessage($attribute, $rule)
	{
		$lowerRule = snake_case($rule);

		$key = "validation.{$lowerRule}";

		return $this->getInlineMessage(
			$attribute, $lowerRule
		) ?: $key;
	}
        
        /**
	 * Get the inline message for a rule if it exists.
	 *
	 * @param  string  $attribute
	 * @param  string  $lowerRule
	 * @param  array   $source
	 * @return string
	 */
	protected function getInlineMessage($attribute, $lowerRule, $source = null)
	{
		$source = $source ?: $this->customMessages;

		$keys = array("{$attribute}.{$lowerRule}", $lowerRule);

		// First we will check for a custom message for an attribute specific rule
		// message for the fields, then we will check for a general custom line
		// that is not attribute specific. If we find either we'll return it.
		foreach ($keys as $key)
		{
			if (isset($source[$key])) return $source[$key];
		}
	}
        
        /**
	 * Replace all error message place-holders with actual values.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function doReplacements($message, $attribute, $rule, $parameters)
	{
                if (strpos($message, 'validation.') === 0){
                        static $validationCache;
                        if (empty($validationCache)){
                                $validationCache['validation'] = @include_once(__DIR__.DIRECTORY_SEPARATOR.'validation.php');
                        }
                        $message = array_get($validationCache, $message);
                }
                
		$message = str_replace(':attribute', $this->getAttribute($attribute), $message);

		if (isset($this->replacers[snake_case($rule)]))
		{
			$message = $this->callReplacer($message, $attribute, snake_case($rule), $parameters);
		}
		elseif (method_exists($this, $replacer = "replace{$rule}"))
		{
			$message = $this->$replacer($message, $attribute, $rule, $parameters);
		}

		return $message;
	}
        
        /**
	 * Transform an array of attributes to their displayable form.
	 *
	 * @param  array  $values
	 * @return array
	 */
	protected function getAttributeList(array $values)
	{
		$attributes = array();

		// For each attribute in the list we will simply get its displayable form as
		// this is convenient when replacing lists of parameters like some of the
		// replacement functions do when formatting out the validation message.
		foreach ($values as $key => $value)
		{
			$attributes[$key] = $this->getAttribute($value);
		}

		return $attributes;
	}
        
        /**
	 * Get the displayable name of the attribute.
	 *
	 * @param  string  $attribute
	 * @return string
	 */
	protected function getAttribute($attribute)
	{

		$key = "validation.attributes.{$attribute}";

		// If no language line has been specified for the attribute all of the
		// underscores are removed from the attribute name and that will be
		// used as default versions of the attribute's displayable names.
		return str_replace('_', ' ', snake_case($attribute));
	}
        
        /**
	 * Get the displayable name of the value.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return string
	 */
	public function getDisplayableValue($attribute, $value)
	{

		$key = "validation.values.{$attribute}.{$value}";

		return $value;
	}
        
        /**
	 * Replace all place-holders for the between rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceBetween($message, $attribute, $rule, $parameters)
	{
		return str_replace(array(':min', ':max'), $parameters, $message);
	}

	/**
	 * Replace all place-holders for the digits rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceDigits($message, $attribute, $rule, $parameters)
	{
		return str_replace(':digits', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the digits (between) rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceDigitsBetween($message, $attribute, $rule, $parameters)
	{
		return $this->replaceBetween($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the size rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceSize($message, $attribute, $rule, $parameters)
	{
		return str_replace(':size', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the min rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceMin($message, $attribute, $rule, $parameters)
	{
		return str_replace(':min', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the max rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceMax($message, $attribute, $rule, $parameters)
	{
		return str_replace(':max', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the in rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceIn($message, $attribute, $rule, $parameters)
	{
		foreach ($parameters as &$parameter)
		{
			$parameter = $this->getDisplayableValue($attribute, $parameter);
		}

		return str_replace(':values', implode(', ', $parameters), $message);
	}

	/**
	 * Replace all place-holders for the not_in rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceNotIn($message, $attribute, $rule, $parameters)
	{
		return $this->replaceIn($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the mimes rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceMimes($message, $attribute, $rule, $parameters)
	{
		return str_replace(':values', implode(', ', $parameters), $message);
	}

	/**
	 * Replace all place-holders for the required_with rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceRequiredWith($message, $attribute, $rule, $parameters)
	{
		$parameters = $this->getAttributeList($parameters);

		return str_replace(':values', implode(' / ', $parameters), $message);
	}

	/**
	 * Replace all place-holders for the required_with_all rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceRequiredWithAll($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the required_without rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceRequiredWithout($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the required_without_all rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceRequiredWithoutAll($message, $attribute, $rule, $parameters)
	{
		return $this->replaceRequiredWith($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the required_if rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceRequiredIf($message, $attribute, $rule, $parameters)
	{
		$parameters[1] = $this->getDisplayableValue($parameters[0], array_get($this->data, $parameters[0]));

		$parameters[0] = $this->getAttribute($parameters[0]);

		return str_replace(array(':other', ':value'), $parameters, $message);
	}

	/**
	 * Replace all place-holders for the same rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceSame($message, $attribute, $rule, $parameters)
	{
		return str_replace(':other', $this->getAttribute($parameters[0]), $message);
	}

	/**
	 * Replace all place-holders for the different rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceDifferent($message, $attribute, $rule, $parameters)
	{
		return $this->replaceSame($message, $attribute, $rule, $parameters);
	}

	/**
	 * Replace all place-holders for the date_format rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceDateFormat($message, $attribute, $rule, $parameters)
	{
		return str_replace(':format', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the before rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceBefore($message, $attribute, $rule, $parameters)
	{
		if ( ! (strtotime($parameters[0])))
		{
			return str_replace(':date', $this->getAttribute($parameters[0]), $message);
		}

		return str_replace(':date', $parameters[0], $message);
	}

	/**
	 * Replace all place-holders for the after rule.
	 *
	 * @param  string  $message
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array   $parameters
	 * @return string
	 */
	protected function replaceAfter($message, $attribute, $rule, $parameters)
	{
		return $this->replaceBefore($message, $attribute, $rule, $parameters);
	}
        
}

if (!function_exists('array_get')) {

        /**
         * Get an item from an array using "dot" notation.
         *
         * @param  array   $array
         * @param  string  $key
         * @param  mixed   $default
         * @return mixed
         */
        function array_get($array, $key, $default = null) {
                if (is_null($key)) {
                        return $array;
                }
                if (isset($array[$key])) {
                        return $array[$key];
                }
                foreach (explode('.', $key) as $segment) {
                        if (!is_array($array) || !array_key_exists($segment, $array)) {
                                return $default instanceof Closure ? $default() : $default;
                        }
                        $array = $array[$segment];
                }
                return $array;
        }

}

if (!function_exists('studly')) {

        function studly($value) {
                static $studlyCache = array();
                $key = $value;
                if (isset($studlyCache[$key])) {
                        return $studlyCache[$key];
                }
                $value = ucwords(str_replace(array('-', '_'), ' ', $value));
                return $studlyCache[$key] = str_replace(' ', '', $value);
        }

}

if (!function_exists('snake')) {

        function snake_case($value, $delimiter = '_') {
                static $snakeCache = array();
                $key = $value . $delimiter;

                if (isset($snakeCache[$key])) {
                        return $snakeCache[$key];
                }

                if (!ctype_lower($value)) {
                        $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $value));
                }

                return $snakeCache[$key] = $value;
        }

}

if (!function_exists('fileext')) {
        function fileext($filename, $check = false) {
                if (is_file($filename)) {
                        if (!$check)
                                return pathinfo($filename, PATHINFO_EXTENSION);
                        $file = fopen($filename, "rb");
                        $bin = fread($file, 2); //只读2字节  
                        fclose($file);
                } else {
                        $bin = substr($filename, 0, 2);
                }
                $strInfo = @unpack("C2chars", $bin);
                $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
                $fileType = '';
                switch ($typeCode) {
                        case 7790:
                                $fileType = 'exe';
                                break;
                        case 7784:
                                $fileType = 'midi';
                                break;
                        case 8297:
                                $fileType = 'rar';
                                break;
                        case 8075:
                                $fileType = 'zip';
                                break;
                        case 255216:
                                $fileType = 'jpg';
                                break;
                        case 7173:
                                $fileType = 'gif';
                                break;
                        case 6677:
                                $fileType = 'bmp';
                                break;
                        case 13780:
                                $fileType = 'png';
                                break;
                        default:
                                $fileType = '';
                }

                //Fix  
                if ($strInfo['chars1'] == '-1' AND $strInfo['chars2'] == '-40')
                        return 'jpg';
                if ($strInfo['chars1'] == '-119' AND $strInfo['chars2'] == '80')
                        return 'png';

                return $fileType;
        }
}
