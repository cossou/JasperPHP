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
    protected $resource_directory; // Path to report resource dir or jar file

    function __construct($resource_dir = false)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
           $this->windows = true;

        if (!$resource_dir) {
            $this->resource_directory = __DIR__ . "/../../../../../";
        } else {
            if (!file_exists($resource_dir))
                throw new \Exception("Invalid resource directory", 1);

            $this->resource_directory = $resource_dir;
        }
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

        $command .= " compile ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        $this->redirect_output  = $redirect_output;
        $this->background       = $background;
        $this->the_command      = escapeshellcmd($command);

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

        $command .= " process ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        if( is_array($format) )
            $command .= " -f " . join(" ", $format);
        else
            $command .= " -f " . $format;

        // Resources dir
        $command .= " -r " . $this->resource_directory;

        if( count($parameters) > 0 )
        {
            $command .= " -P";
            foreach ($parameters as $key => $value)
            {
                if( is_string($value) )
                    $command .= " $key=\"$value\"";
                else
                    $command .= " $key=$value";
            }
        }

        if( count($db_connection) > 0 )
        {
            $command .= " -t " . $db_connection['driver'];

            if( isset($db_connection['username']) && !empty($db_connection['username']) )
                $command .= " -u " . $db_connection['username'];

            if( isset($db_connection['password']) && !empty($db_connection['password']) )
                $command .= " -p " . $db_connection['password'];

            if( isset($db_connection['host']) && !empty($db_connection['host']) )
                $command .= " -H " . $db_connection['host'];

            if( isset($db_connection['database']) && !empty($db_connection['database']) )
                $command .= " -n " . $db_connection['database'];

            if( isset($db_connection['port']) && !empty($db_connection['port']) )
                $command .= " --db-port " . $db_connection['port'];

            if( isset($db_connection['jdbc_driver']) && !empty($db_connection['jdbc_driver']) )
                $command .= " --db-driver " . $db_connection['jdbc_driver'];

            if( isset($db_connection['jdbc_url']) && !empty($db_connection['jdbc_url']) )
                $command .= " --db-url " . $db_connection['jdbc_url'];

            if ( isset($db_connection['jdbc_dir']) && !empty($db_connection['jdbc_dir']) ) 
                $command .= ' --jdbc-dir ' . $db_connection['jdbc_dir'];

            if ( isset($db_connection['db_sid']) && !empty($db_connection['db_sid']) )
                $command .= ' --db-sid ' . $db_connection['db_sid'];

            if ( isset($db_connection['json_query']) && !empty($db_connection['json_query']) )
                $command .= ' --json-query ' . $db_connection['json_query'];

            if ( isset($db_connection['data_file']) && !empty($db_connection['data_file']) )
                $command .= ' --data-file ' . $db_connection['data_file'];

        }

        $this->redirect_output  = $redirect_output;
        $this->background       = $background;
        $this->the_command      = escapeshellcmd($command);

        return $this;
    }

    public function list_parameters($input_file)
    {
        if(is_null($input_file) || empty($input_file))
            throw new \Exception("No input file", 1);

        $command = __DIR__ . $this->executable;

        $command .= " list_parameters ";

        $command .= $input_file;

        $this->the_command = escapeshellcmd($command);

        return $this;
    }

    public function output()
    {
        return escapeshellcmd($this->the_command);
    }

    public function execute($run_as_user = false)
    {
        if( $this->redirect_output && !$this->windows)
            $this->the_command .= " 2>&1";

        if( $this->background && !$this->windows )
            $this->the_command .= " &";

        if( $run_as_user !== false && strlen($run_as_user) > 0 && !$this->windows )
            $this->the_command = "su -c \"{$this->the_command}\" {$run_as_user}";

        $output     = array();
        $return_var = 0;

        exec($this->the_command, $output, $return_var);

        if( $return_var != 0 && isset($output[0]) )
            throw new \Exception($output[0], 1);
        elseif( $return_var != 0 ) 
            throw new \Exception("Your report has an error and couldn't be processed! Try to output the command using the function `output();` and run it manually in the console.", 1);

        return $output;
    }
    
    public function setResourceDirectory($resourceDirectory) 
    {
        $this->resource_directory = $resourceDirectory;
    }    
}
