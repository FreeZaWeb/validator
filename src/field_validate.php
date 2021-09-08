<?

class field_validate{


    protected static $_instant = null;
    protected static $method = null;
    protected static $custom_fields = [];

    protected $key = null;
    protected $input = null;

    protected $arr_dump_input_base = null;


    protected $required = false;
    protected $not_empty = false;

    protected $checked = false;

    protected $errors = [];

    protected $dateformat = null;
    protected $format_to_string = false;

    ///////////////////////////////////////

    public static function POST($key){
        self::$method = 'POST';
        return new self($key);
    }

    public static function GET($key){
        self::$method = 'GET';
        return new self($key);
    }


    public static function CUSTOM($custom_fields_array, $key){
        self::$method = 'CUSTOM';
        self::$custom_fields = $custom_fields_array;
        return new self($key);
    }

    public function __construct($key){

        $this->key = $key;

        if(self::$method == 'POST'){
            if(isset($_POST[$key])){
                $this->input = $_POST[$key];
            }else{
                $this->input = null;
            }
        }

        if(self::$method == 'GET'){
            if(isset($_GET[$key])){
                $this->input = $_GET[$key];
            }else{
                $this->input = null;
            }
        }

        if(self::$method == 'CUSTOM'){
            if(isset(self::$custom_fields[$key])){
                $this->input = self::$custom_fields[$key];
            }else{
                $this->input = null;
            }
        }

    }

    public function req(){
        $this->required();
        return $this;
    }

    public function required(){

        $this->required = true;

        if(self::$method == 'POST') {
            if (!isset($_POST[$this->key])) {
                $this->add_error('required', 'false');
            }
        }

        if(self::$method == 'GET') {
            if (!isset($_GET[$this->key])) {
                $this->add_error('required', 'false');
            }
        }


        if(self::$method == 'CUSTOM') {
            if (!isset(self::$custom_fields[$this->key])) {
                $this->add_error('required', 'false');
            }
        }

        return $this;
    }

    public function not_empty(){
        $this->not_empty = true;
    }

    public function checked(){

        if(self::$method == 'POST') {
            if(isset($_POST[$this->key])){
                $this->checked = true;
            }
        }

        return $this;
    }

    public function arr($params = null){

        if(!is_array($this->input)){
            $this->add_error('not_array', 'not_array');
        }

        if($params[0]){

            $this->arr_dump_input_base = $this->input;

            foreach($this->arr_dump_input_base as $item){

                $this->input = $item;

                if(in_array($params[0], ['int', 'float', 'str', 'bool'])){

                    $sub_fields = null;
                    $rule_method_name = $params[0];
                    $sub_fields = $this->$rule_method_name();

                    if($sub_fields->errors){
                        $this->add_error('array_error', 'not_'.$params[0]);
                    }

                }else{
                    $this->add_error('array_error_type', 'array_validator_dont_have_rule_'.$params[0]);
                }

            }
            $this->input = $this->arr_dump_input_base;


        }

        return $this;
    }


    public function enum($params = null){

        if(!is_array($params[0])){
            $this->add_error('enum_param', 'not_array');
        }else{
            if(!in_array($this->input, $params[0])){
                $this->add_error('enum', 'not correct value');
            }
        }

        return $this;
    }

    public function str(){
        $this->input = trim(htmlspecialchars($this->input));
        return $this;
    }

    public function bool(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if(!is_bool($this->input)){
            $this->add_error('not_bool', 'not_bool');
            return $this;
        }

        if($this->input == true){
            $this->input = 'true';
        }else{
            $this->input = 'false';
        }

        return $this;
    }

    public function int(){

        if(!is_int($this->input)){
            $this->add_error('int', 'not_int');
        }

        $this->input = filter_var((int)$this->input, FILTER_VALIDATE_INT);
        return $this;
    }

    public function float($params = null){

        $this->input = str_replace(',', '.', $this->input);

        if(!$params == null && count($params) == 2){


            $this->input = filter_var($this->input, FILTER_VALIDATE_FLOAT);

            if(in_array ('min', $params)){

                if($this->input < $params[1]){
                    $this->input = '';
                    $this->add_error('float_range', 'false');
                }
            }

            if(in_array ('max', $params)){
                if($this->input > $params[1]){
                    $this->input = '';
                    $this->add_error('float_range', 'false');
                }
            }

            if(is_numeric($params[0]) && is_numeric($params[1])){
                if($this->input < $params[0] || $this->input > $params[1]){
                    $this->input = '';
                    $this->add_error('float_range', 'false');
                }
            }




        }else{

            $this->input = filter_var($this->input, FILTER_VALIDATE_FLOAT);
        }


        if(!is_float($this->input)){
            $this->add_error('float', 'not_float');
        }


        return $this;
    }

    public function date($params = null){
        $this->format_to_string = true;

        if(isset($params[0])){
            $format = $params[0];
        }
        if(empty($format)){
            $format = 'Y-m-d H:i:s';
        }

        $this->dateformat = $format;

        $d = \DateTime::createFromFormat($format, $this->input);

        if($d){
            if($d && $d->format($format) == $this->input){
                $this->input = $d;
            }else{
                if($this->required == true || $this->not_empty == true){
                    $this->add_error('format', 'false');
                }

            }
        }else{
            $this->add_error('format', 'false');
        }



        //$this->input = new \DateTime($this->input);
        return $this;
    }

    public function min($params = null){
        if(!$params){
            $this->add_error('min', 'not selected min chars in validator');
        }

        if(!is_array($this->input)){
            if(strlen($this->input) < (int) $params[0]){
                $this->add_error('min', 'str < min');
            }
        }else{
            if(count($this->input) <= (int) $params[0]){
                $this->add_error('min', 'array_items < min');
            }
        }

    }

    public function max($params = null){
        if(!$params){
            $this->add_error('max', 'not selected min chars in validator');
        }
        if(!is_array($this->input)) {
            if (strlen($this->input) > (int)$params[0]) {
                $this->add_error('max', 'str > max');
            }
        }else{
            if(count($this->input) >= (int) $params[0]){
                $this->add_error('max', 'array_items > max');
            }
        }
    }

    public function url(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_URL);
        return $this;
    }

    public function email(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_EMAIL);
        return $this;
    }

    public function phone($params = null){

        if($params){

            if(is_array($params)){
                $formetter_fn_name = $params[0];
            }else{
                $formetter_fn_name = $params;
            }

            $this->input = $formetter_fn_name($this->input);
        }else{
            $this->input = $this->unformatphone($this->input);
        }


        return $this;
    }

    protected function unformatphone($phone){
        $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
        $phone = trim($phone);
        if($phone[0] == '+'){
            unset($phone[0]);
            $phone[1] = 7;
        }
        if($phone[0] == 8){
            $phone[0] = 7;
        }
        return $phone;
    }



    public function domain(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        return $this;
    }

    public function ip(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_IP);
        return $this;
    }

    public function mac(){
        $this->input = filter_var($this->input, FILTER_VALIDATE_MAC);
        return $this;
    }



    public function get_obj(){

        $this->format_to_string = false;
        return $this;
    }

    protected function format_to_str(){

        if(method_exists($this->input, 'format')){
            $this->input = $this->input->format($this->dateformat);
        }
        return $this;
    }

    protected function _not_empty(){

        if(empty($this->input)){
            $this->add_error('required', 'false');
        }

        return $this;
    }



    public function finish(){

        //debug($this);

        if($this->required == true){
            $this->_not_empty();
        }
        if($this->not_empty == true){
            $this->_not_empty();
        }


        if(!empty($this->dateformat) && $this->format_to_string == true){
            $this->format_to_str();
        }


        return $this;
    }

    public function get_errors(){
        return $this->errors;
    }

    public function add_error($key, $value){
        $this->errors[$key] = $value;
        return $this;
    }

    public function on_error_replace($data){
        if(!empty($this->errors)){
            $this->errors = [];
            $this->input = $data;
        }
        return $this;
    }

    public function get_field_data(){
        return $this->input;
    }

}

