<?php
namespace JasperPHP;

class JasperPHP
{
    protected $executable = "/../JasperStarter/bin/jasperstarter";
    protected $the_command;

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
            throw new Exception("No input file", 1);
            
        $command = __DIR__ . $this->executable;
        
        $command .= " cp ";

        $command .= $input_file;

        if( $output_file !== false )
            $command .= " -o " . $output_file;

        if( $redirect_output )
            $command .= " > /dev/null 2>&1";

        if( $background )
            $command .= " &";

        $this->the_command = $command;
        return $this;
    }

    public function process($input_file, $output_file = false, $format = array("pdf"), $parameters = array(), $db_connection = array(), $background = true, $redirect_output = true)
    {
        if(is_null($input_file) || empty($input_file))
            throw new Exception("No input file", 1);

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
            $command .= " -t " . $db_connection['type'];
            $command .= " -u " . $db_connection['user'];
            
            if( isset($db_connection['password']) )
                $command .= " -p " . $db_connection['password'];
            
            $command .= " -H " . $db_connection['host'];
            $command .= " -n " . $db_connection['database'];
        }
        
        if( $redirect_output )
            $command .= " > /dev/null 2>&1";

        if( $background )
            $command .= " &";

        $this->the_command = $command;
        return $this;
    }

    public function output()
    {
        return $this->the_command;
    }

    public function execute()
    {
        return exec($this->the_command);
    }
}
