<?php
namespace JasperPHP;

class JasperPHP
{
    protected $executable = "/../JasperStarter/bin/jasperstarter";
    protected $the_command;
    protected $redirect_output;
    protected $background;
    protected $windows = false;
    protected $formats = array('pdf', 'rtf', 'xls', 'xlsx', 'docx', 'odt', 'ods', 'pptx', 'csv', 'html', 'xhtml', 'xml', 'jrprint');

    function __construct()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
           $this->windows = true;
    }

    public static function __callStatic($method, $parameters)
    {
        // Create a new instance of the called class, in this case it is Post
        $model = get_called_class();

        // Call the requested method on the newly created object
        return call_user_func_array(array(new $model, $method), $parameters);
    }

    public function compile($input_file, $output_file = false, $background = true, $redirect_output = true)
    {
        if(is_null($input_file) || empty($input_file))
            throw new \Exception("No input file", 1);

        $command = __DIR__ . $this->executable;

        $command .= " cp ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        $this->redirect_output  = $redirect_output;
        $this->background       = $background;
        $this->the_command      = $command;

        return $this;
    }

    public function process($input_file, $output_file = false, $format = array("pdf"), $parameters = array(), $db_connection = array(), $background = true, $redirect_output = true)
    {
        if(is_null($input_file) || empty($input_file))
            throw new \Exception("No input file", 1);

        if( is_array($format) )
        {
            foreach ($format as $key)
            {
                if( !in_array($key, $this->formats))
                    throw new \Exception("Invalid format!", 1);
            }
        } else {
            if( !in_array($format, $this->formats))
                    throw new \Exception("Invalid format!", 1);
        }

        $command = __DIR__ . $this->executable;

        $command .= " pr ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        if( is_array($format) )
            $command .= " -f " . join(" ", $format);
        else
            $command .= " -f " . $format;

        // Resources dir
        $command .= " -r " . __DIR__ . "/../../../../../";

        if( count($parameters) > 0 )
        {
            $command .= " -P";
            foreach ($parameters as $key => $value)
            {
                $command .= " " . $key . "=" . $value;
            }
        }

        if( count($db_connection) > 0 )
        {
            $command .= " -t " . $db_connection['driver'];
            $command .= " -u " . $db_connection['username'];

            if( isset($db_connection['password']) && !empty($db_connection['password']) )
                $command .= " -p " . $db_connection['password'];

            $command .= " -H " . $db_connection['host'];
            $command .= " -n " . $db_connection['database'];

            if (isset($db_connection['jdbc-dir'])) {
                $command .= ' --jdbc-dir ' . $db_connection['jdbc-dir'];
            }
            if (isset($db_connection['db-sid'])) {
                $command .= ' --db-sid ' . $db_connection['db-sid'];
            }
        }

        $this->redirect_output  = $redirect_output;
        $this->background       = $background;
        $this->the_command      = $command;

        return $this;
    }

    public function output()
    {
        return $this->the_command;
    }

    public function execute($run_as_user = false)
    {
        if( $this->redirect_output && !$this->windows)
            $this->the_command .= " > /dev/null 2>&1";

        if( $this->background && !$this->windows )
            $this->the_command .= " &";

        if( $run_as_user !== false && strlen($run_as_user > 0) && !$this->windows )
            $this->the_command = "su -u " . $run_as_user . " -c \"" . $this->the_command . "\"";

        $output     = array();
        $return_var = 0;

        exec($this->the_command, $output, $return_var);

        if($return_var != 0) {
            throw new \Exception("There was and error executing the report! Time to check the logs!", 1);
        }

        return $output;
    }

    /**
     * Return list parameters from report
     * @param string $filePath
     * @throws \Exception
     * @return array
     */
    public function listParameters($filePath, $run_as_user = false)
    {
        if (!$filePath) {
            throw new \Exception('Path file empty');
        }

        if (!file_exists($filePath)) {
            throw new \Exception('Report file not found [' . $filePath . ']');
        }

        $command = __DIR__ . $this->executable;
        $command .= ' list_parameters ' . $filePath;

        $this->redirect_output  = false;
        $this->background       = false;
        $this->the_command      = $command;

        $parameters = array();
        $response = $this->execute($run_as_user);

        if ($response) {
            foreach ($response as $param) {
                $keywords = preg_split("/[\s,]+/", $param);
                $parameters[] = array('name' => $keywords[1], 'type' => $keywords[2]);
            }
        }
        return $parameters;
    }
}
