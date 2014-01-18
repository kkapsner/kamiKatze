<?php

/*
 * CalendarObject declaration file.
 */

/**
 * A CalendarObject is an abstract representation of a calendar object in 
 * iCalendar format
 *
 * @author kkapsner
 */
class CalendarObject extends ViewableImplementation implements Countable, IteratorAggregate{
	/**
	 * Definitions of the different calendar elements.
	 * @var array
	 */
	private static $elementDefinition = array(
		"VCALENDAR" => array(
			"properties" => array(
				"required" => array("version", "prodid"),
				"optional" => array(
					"once" => array("calscale", "method"),
					"onceExclusive" => array(),
					"multi" => array()
				),
			),
			"elements" => array("VEVENT", "VTODO", "VJOURNAL", "VFREEBUSY", "VTIMEZONE")
		),
		"VEVENT" => array(
			"properties" => array(
				"required" => array(),
				"optional" => array(
					"once" => array("class", "created", "description", "dtstart", "geo", "last-mod", "location", "organizer", "priority", "dtstamp", "seq", "status", "summary", "transp", "uid", "url", "recurid"),
					"onceExclusive" => array(array("dtend", "duration")),
					"multi" => array("attach", "attendee", "categories", "comment", "contact", "exdate", "exrule", "rstatus", "related", "resources", "rdate", "rrule")
				)
			),
			"elements" => array("VALARM")
		),
		"VTODO" => array(
			"properties" => array(
				"required" => array(),
				"optional" => array(
					"once" => array("class", "completed", "created", "description", "dtstamp", "dtstart", "geo", "last-mod", "location", "organizer", "percent", "priority", "recurid", "seq", "status", "summary", "uid", "url"),
					"onceExclusive" => array(array("due", "duration")),
					"multi" => array("attach", "attendee", "categories", "comment", "contact", "exdate", "exrule", "rstatus", "related", "resources", "rdate", "rrule")
				)
			),
			"elements" => array("VALARM")
		),
		"VJOURNAL" => array(
			"properties" => array(
				"required" => array(),
				"optional" => array(
					"once" => array("class", "created", "description", "dtstart", "dtstamp", "last-mod", "organizer", "recurid", "seq", "status", "summary", "uid", "url"),
					"onceExclusive" => array(),
					"multi" => array("attach", "attendee", "categories", "comment", "contact", "exdate", "exrule", "related", "rdate", "rrule", "rstatus")
				)
			),
			"elements" => array()
		),
		"VFREEBUSY" => array(
			"properties" => array(
				"required" => array(),
				"optional" => array(
					"once" => array("contact", "dtstart", "dtend", "duration", "dtstamp", "organizer", "uid", "url"),
					"onceExclusive" => array(),
					"multi" => array("attendee", "comment", "freebusy", "rstatus")
				)
			),
			"elements" => array()
		),
		"VTIMEZONE" => array(
			"properties" => array(
				"required" => array("tzid"),
				"optional" => array(
					"once" => array("last-mod", "tzurl"),
					"onceExclusive" => array(),
					"multi" => array()
				)
			),
			"elements" => array("STANDARD", "DAYLIGHT")
		),
		"STANDARD" => array(
			"properties" => array(
				"required" => array("dtstart", "tzoffsetto", "tzoffsetfrom"),
				"optional" => array(
					"once" => array(),
					"onceExclusive" => array(),
					"multi" => array("comment", "rdate", "rrule", "tzname")
				)
			),
			"elements" => array()
		),
		"DAYLIGHT" => array(
			"properties" => array(
				"required" => array("dtstart", "tzoffsetto", "tzoffsetfrom"),
				"optional" => array(
					"once" => array(),
					"onceExclusive" => array(),
					"multi" => array("comment", "rdate", "rrule", "tzname")
				)
			),
			"elements" => array()
		),
		"VALARM" => array(
			"properties" => array(
				"required" => array(),
				"optional" => array(
					"once" => array(),
					"onceExclusive" => array(),
					"multi" => array()
				)
			),
			"elements" => array()
		)
	);
	
	/**
	 * Name of the element.
	 * @var String
	 */
	protected $name;
	
	/**
	 * Constructor for an CalendarObject
	 * @param String $name the name of the element (i.e. "VCALENDAR" or "VEVENT"...
	 */
	public function __construct($name){
		$this->name = $name;
	}

	/**
	 * "Getter" for required properties
	 * @return array Array of all required properties
	 */
	public function getRequiredProperties(){
		return self::$elementDefinition[$this->getName()]["properties"]["required"];		
	}

	/**
	 * "Getter" for optional properties
	 * @return array Array of all optional properties
	 */
	public function getOptionalProperties(){
		$optional = self::$elementDefinition[$this->getName()]["properties"]["optional"];
		$ret = array_merge(
			$optional["once"],
			$optional["multi"]
		);
		foreach ($optional["onceExclusive"] as $exclusive){
			$ret = array_merge($ret, $exclusive);
		}
		return $ret;
	}

	/**
	 * Checks if a specific child can be added to a calendar object
	 * 
	 * @param CalendarObject $child the child to be tested
	 * @return boolean if the child can be inserted
	 */
	public function isValidChild(CalendarObject $child){
		return in_array($child->getName(), self::$elementDefinition[$this->getName()]["elements"]);
	}

	/**
	 * "Getter" for the objects type name
	 * @return String Name of the object in the iCalendar format
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * internal storage for properties
	 * @var CalendarProperty[]
	 */
	protected $properties = array();

	/**
	 * magic getter function. Returns the property if existing or null otherwise.
	 * @param String $name Name of the property
	 * @return CalendarProperty|null Return the property object if existing and null otherwise.
	 */
	public function __get($name){
		$name = strToLower($name);
		if (array_key_exists($name, $this->properties)){
			return $this->properties[$name];
		}
		else {
			return null;
		}
	}

	/**
	 * magic setter function. Set
	 * @param String $name Name of the property
	 * @param mixed $value value of the property
	 */
	public function __set($name, $value){
		$name = strToLower($name);
		if ($value instanceof CalendarProperty){
			$this->properties[$name] = $value;
		}
		else {
			if (array_key_exists($name, $this->properties)){
				$this->properties[$name]->value = $value;
			}
			else {
				$this->properties[$name] = new CalendarProperty($name, $value);
			}
		}
	}

	/**
	 * Internal storage for all child elements
	 * @var array
	 */
	protected $children = array();

	/**
	 * Adds an calendar object as a child object.
	 * @param CalendarObject|String $child
	 * @return CalendarObject|null Returns the added object or null at failure.
	 */
	public function addChild($child){
		if (is_string($child)){
			$child = new self($child);
		}
		if ($this->isValidChild($child)){
			$this->children[] = $child;
			return $child;
		}
		else {
			return null;
		}
	}

	/**
	 * Countable interface
	 */
	public function count(){
		return count($this->children);
	}

	/**
	 * IteratorAggregate interface
	 */
	
	/**
	 * @inherit
	 */
	public function getIterator(){
		return new ArrayIterator($this->children);
	}
}

?>
