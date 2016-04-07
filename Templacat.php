<?php
    
    namespace Templacat;

    /**
        * Templacat
        * Simple PHP templating system
        *
        * @version 1.0
        * @author Quentin Bouteiller
        * @license GNU GPL v3
    */
    class Templacat
    {
        private $tpl_buffer;
        private $tpl_vars;

        private $tpl_directory;
        private $tpl_extension;

        /**
            * Class constructor
            *
            * @param  string  $td  Path where the template files can be found
            * @param  string  $te  Template files extension
            * @return bool
        */
        public function __construct($td = 'template', $te = 'tpl')
        {
            // Set the directory where the templates files can be found
            $this->tpl_directory = $td;

            // Set the extension for the templates files
            $this->tpl_extension = $te;

            // Initialize the other variables;
            $this->tpl_buffer = null;
            $this->tpl_vars = array();
        }

        /**
            * Load a template file into the template buffer or into a variable
            *
            * @param  string  $file      Filename of the template, without the directory nor the extension
            * @param  string  $var_name  Variable where the template should be stored (optional)
            * @return bool
        */
        public function load_template($file, $var_name = null)
        {
            // Sanitize the $file variable
            $file = basename($file, $this->tpl_extension);

            // Generate the template file location
            $template_file_location = $this->tpl_directory . '/' . $file . '.' . $this->tpl_extension;

            // If the file exists
            if(is_file($template_file_location))
            {
                // Then include its content to...
                if(is_null($var_name)) // ... the template buffer 
                    $this->tpl_buffer .= file_get_contents($template_file_location);
                else // ... a variable
                    $this->set_variable($var_name, file_get_contents($template_file_location));

                // Then return true
                return true;
            }
            else
                // Otherwyse just return false
                return false;
        }

        /**
            * Render the template with all the variables set
            *
            * @param  string  $var_name     Name of the variable to set
            * @param  any     $var_content  Content of the variable to set
            * @return void
        */
        public function set_variable($var_name, $var_content)
        {
            // The variables array only have lowercase indexes (so we can make case-insensitive variables)
            $var_name = strtolower($var_name);

            // Write the content of the variable into the variables array
            $this->tpl_vars[$var_name] = $var_content;
        }

        /**
            * Render the template with all the variables set
            *
            * @return string
        */
        public function render()
        {
            // Return the parsed version of the template buffer
            // Whereas all the variables are replaced by their content
            // We render it twice to also replace variables into variables (such as template variables)
            $render_1 = preg_replace_callback('/\{\{\%([a-z0-9-_]+)\%\}\}/i', 'self::render_callback', $this->tpl_buffer);
            return preg_replace_callback('/\{\{\%([a-z0-9-_]+)\%\}\}/i', 'self::render_callback', $render_1);
        }

        /**
            * Callback used for setting the variables in the render() function
            *
            * @return string
        */
        private function render_callback($matches)
        {
            // The variables array only have lowercase indexes
            $var_name = strtolower($matches[1]);

            // If the variable is defined
            if(isset($this->tpl_vars[$var_name]))
                // Then we return its content
                return $this->tpl_vars[$var_name];
            else
                // Otherwyse we just return an empty string
                return '';
        }

    }
?>