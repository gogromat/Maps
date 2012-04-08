<?php
/**
 * Attribute Reader - A class for reading attributes/metadata from PHP classes
 */
class AttributeReader
{
	/**
	 * List of attributes
	 *
	 * @var array
	 */
	public $attributes=null;
	
	/**
	 * Constructor
	 *
	 * @param array $attributes
	 */
	protected function __construct($Attributes)
	{
		foreach(explode(",",$Attributes) as $attribute)
		{
			if ($attribute != '')
			{
				$key=substr($attribute,0,strpos($attribute,'='));
				$item=substr($attribute,strpos($attribute,'=')+1);
				
				$this->attributes[$key]=$item;
			}
		}
	}
	
	/**
	 * Magic getter so that attributes appear as properties of the reader
	 *
	 * @param string $prop_name Name of the property
	 * @return array The metadata for the given property
	 */
	public function __get($prop_name)
	{
		if (isset($this->attributes[$prop_name]))
			return $this->attributes[$prop_name];
	}
	
	/**
	 * Parses YAML from the doc comments for a class or method, returning the yaml as a string
	 *
	 * @param string $doc_comment The doc comment parsed using PHP's reflection
	 * @return string The parsed out YAML.
	 */
	private static function ParseDocComments($doc_comment)
	{
		$comments=explode(",",$doc_comment);
		
		$yaml='';
		foreach($comments as $comment)
		{
			$line=trim($comment);
			if (strpos($line,'/**')===0)
				$line=substr($line,6);
			if (strpos($line,']]')>0)
				$line=substr($line,0,strpos($line,']]'));
			$yaml.=$line.",";
		}
		
		return $yaml;
	}
	
	/**
	 * Fetches the metadata for a method of a class
	 *
	 * @param mixed $class An instance of the class or it's name as a string
	 * @param string $method The name of the method 
	 * @return AttributeReader An attribute reader instance
	 */
	public static function MethodAttributes($class,$method)
	{
		$method=new ReflectionMethod($class,$method);
		$yaml=AttributeReader::ParseDocComments($method->getDocComment());
		
		return new AttributeReader($yaml);
	}
	
	/**
	 * Fetches the metadata for a class
	 *
	 * @param mixed $class An instance of a class or it's name as a string
	 * @return AttributeReader An attribute reader instance
	 */
	public static function ClassAttributes($class)
	{
		$class=new ReflectionClass($class);
		$yaml=AttributeReader::ParseDocComments($class->getDocComment());
		
		return new AttributeReader($yaml);
	}
	
	/**
	 * Fetches the metadata for a property of a class
	 *
	 * @param mixed $class An instance of the class or it's name as a string
	 * @param string $prop The name of the property 
	 * @return AttributeReader An attribute reader instance
	 */
	public static function PropertyAttributes($class,$prop)
	{
		$prop=new ReflectionProperty($class,$prop);
		$yaml=AttributeReader::ParseDocComments($prop->getDocComment());
		
		return new AttributeReader($yaml);
	}
}
