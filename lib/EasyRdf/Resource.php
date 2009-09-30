<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright 
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or 
 *    promote products derived from this software without specific prior 
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * @see EasyRdf_Exception
 */
require_once "EasyRdf/Exception.php";

/**
 * @see EasyRdf_Namespace
 */
require_once "EasyRdf/Namespace.php";

/**
 * Class that represents an RDF resource
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Resource
{
    /** The URI for this resource */
    private $_uri = null;
    
    /** Associative array of properties */
    private $_properties = array();
    
    // This shouldn't be called directly
    public function __construct($uri)
    {
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException(
                "\$uri should be a string and cannot be null or empty"
            );
        }
        
        $this->_uri = $uri;
    }
    
    /** Returns the URI for the resource. */
    public function getUri()
    {
        return $this->_uri;
    }
    
    public function set($property, $values)
    {
        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should be a string and cannot be null or empty"
            );
        }
        
        if ($values == null or (is_array($values) and count($values)==0)) {
            unset( $this->_properties[$property] );
        } else {
            if (!is_array($values)) {
                $values = array($values);
            }
            $this->_properties[$property] = $values;
        }
    }

    public function add($property, $value)
    {
        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should be a string and cannot be null or empty"
            );
        }

        if ($value == null) {
             return null;
        }
        
        # Get the existing values for a property
        if (array_key_exists($property, $this->_properties)) {
            $values = $this->_properties[$property];
        } else {
            $values = array();
        }

        // Add to array of values, if it isn't already there
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!in_array($v, $values)) {
                    array_push($values, $v);
                }
            }
        } else {
            if (!in_array($value, $values)) {
                array_push($values, $value);
            }
        }
        
        return $this->set($property, $values);
    }
    
    public function get($property)
    {
        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should be a string and cannot be null or empty"
            );
        }

        if (isset($this->_properties[$property])) {
            # FIXME: sort values so that we are likely to return the same one?
            return $this->_properties[$property][0];
        } else {
            return null;
        }
    }
    
    public function all($property)
    {
        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should be a string and cannot be null or empty"
            );
        }

        if (isset($this->_properties[$property])) {
            return $this->_properties[$property];
        } else {
            return array();
        }
    }
    
    public function join($property, $glue=' ')
    {
        if (!is_string($property) or $property == null or $property == '') {
            throw new InvalidArgumentException(
                "\$property should be a string and cannot be null or empty"
            );
        }

        return join($glue, $this->all($property));
    }
    
    public function properties()
    {
        return array_keys($this->_properties);
    }
    
    public function isBnode()
    {
        if (substr($this->_uri, 0, 2) == '_:') {
            return true;
        } else {
            return false;
        }
    }
    
    # Return an array of this resource's types
    public function types()
    {
        return $this->all('rdf:type');
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->get('rdf:type');
    }
    
    # Return the namepace that this resource is part of
    public function ns()
    {
        return EasyRdf_Namespace::namespaceOfUri($this->_uri);
    }
    
    public function shorten()
    {
        return EasyRdf_Namespace::shorten($this->_uri);
    }
    
    public function label()
    {
        if ($this->get('rdfs:label')) {
            return $this->get('rdfs:label');
        } else if ($this->get('foaf:name')) {
            return $this->get('foaf:name');
        } else if ($this->get('dc:title')) {
            return $this->get('dc:title');
        } else {
            return EasyRdf_Namespace::shorten($this->_uri); 
        }
    }
    
    public function dump($html=true, $depth=0)
    {
        # FIXME: finish implementing this
        echo '<pre>';
        echo '<b>'.$this->getUri()."</b>\n";
        echo 'Class: '.get_class($this)."\n";
        echo 'Types: '.implode(', ', $this->types())."\n";
        echo "Properties:</i>\n";
        foreach ($this->_properties as $property => $values) {
            echo "  $property => \n";
            foreach ($values as $value) {
                echo "    $value\n";
            }
        }
        echo "</pre>";
    }

    
    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $property = preg_replace(
            '/_/',':', strtolower(substr($name, 3, 1)) . substr($name, 4), 1
        );
        
        switch ($method) {
          case 'get':
              return $this->get($property);
              break;
          
          case 'all':
              return $this->all($property);
              break;
        
          default:
              throw new BadMethodCallException(
                  'Tried to call unknown method '.get_class($this).'::'.$name
              );
              break;
        }
    }
    
    public function __toString()
    {
        return $this->_uri;
    }
}

