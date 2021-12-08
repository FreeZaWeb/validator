<?

class validator extends \stdClass {

    protected static $method = null;
    protected static $custom_fields = [];

    protected   $fields_rules  = [];
    public      $errors = null;
    protected   $fields = [];

    ///////////////////////////////////////

    public static function ALL_POST(array $fields_rules){
        self::$method = 'POST';
        return new self($fields_rules);
    }

    public static function ALL_GET(array $fields_rules){
        self::$method = 'GET';
        return new self($fields_rules);
    }

    public static function CUSTOM(array $custom_fields, array $fields_rules){
        self::$method = 'CUSTOM';

        self::$custom_fields = $custom_fields;
        return new self($fields_rules);

    }

    protected function __construct(array $fields_rules){

        foreach($fields_rules as $f_name => $f_rule){


            $this->fields[$f_name] = null;
            $this->$f_name = null;

            foreach ($f_rule as $f_rule_item){

                if(is_array($f_rule_item)){
                    if(in_array('arr', $f_rule_item)){
                        $this->fields[$f_name] = [0 => null];
                        $this->$f_name = [0 => null];
                    }
                }
            }

        }

        $this->fields_rules = $fields_rules;

        if(self::$method == 'POST') {
            if (!empty($_POST)){
                $this->_validate();
            }
            else{
                $this->errors['POST'] = 'empty';
            }
        }

        if(self::$method == 'GET') {
            if (!empty($_GET)){
                $this->_validate();
            }
            else{
                $this->errors['GET'] = 'empty';
            }
        }


        if(self::$method == 'CUSTOM') {
            if (!empty(self::$custom_fields)){
                $this->_validate();
            }
            else{
                $this->errors['CUSTOM'] = 'empty';
            }
        }

        return $this;

    }

    protected function _validate(){



        foreach($this->fields_rules as $key => $rules) {

            $method = self::$method;
            if($method == 'CUSTOM'){
                $field = field_validate::$method(self::$custom_fields, $key);
            }else{
                $field = field_validate::$method($key);
            }

            foreach ($rules as $rule){

                if(is_array($rule)){

                    $rule_name = $rule[0];

                    unset($rule[0]);
                    $rule_rarams = array_values($rule);

                    if(method_exists($field, $rule_name)){
                        $field->$rule_name($rule_rarams);
                    }else{
                        $this->errors[$key]['not_method_exist'] = $rule_name;
                    }

                }else{

                    if(method_exists($field, $rule)){
                        $field->$rule();
                    }else{
                        $this->errors[$key]['not_method_exist'] = $rule;
                    }

                }


            }

            $field->finish();

            $errors_array = $field->get_errors();

            if(!empty($errors_array)){
                $this->errors[$key] = $errors_array;
            }

            $fdata = $field->get_field_data();

            $this->fields[$key] = $fdata;
            $this->{$key} = $fdata;

        }

    }


    public function on_error_replace(array $data){
        foreach ($data as $key => $value){
            if(isset($this->errors[$key])){
                unset($this->errors[$key]);
                $this->fields[$key] = $value;
            }
        }
    }

    public function add_fields(array $data){
        foreach ($data as $key => $value){
            $this->fields[$key] = $value;
            $this->$key = $value;
        }
    }

    public function delete_field($key){
        unset($this->fields[$key]);
        unset($this->$key);
    }


    public function add_field($key, $value){
        $this->fields[$key] = $value;
        $this->$key = $value;
    }

    public function get_fields(){
        return $this->fields;
    }


    public function POST(){
        if(!empty($_POST)){
            return true;
        }
        return false;
    }

    public function GET(){
        if(!empty($_GET)){
            return true;
        }
        return false;
    }





}