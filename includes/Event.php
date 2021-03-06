<?php

class Event
{
	protected $_eblastFile;
	protected $_eblastImg;
	protected $_eblastPath;
	protected $_eblastLink;
	protected $_eblastHtml;
	protected $_bannerFile;
	protected $_bannerImg;
	protected $_bannerPath;
	protected $_bannerLink;
	protected $_status;

	/*
	 * Set the file from specified property
	 * 
	 * @param  string $type 
	 * @param  array  $file
	 * @return null
	 */
	public function setFile($type, $file)
	{
		$property = "_". $type . "File";
		$this->$property = $file;	
		$this->setImg($type, $file['name']);
	}

	/*
	 * Get the file for specified property
	 * 
	 * @return array
	 */
	public function getFile($type)
	{
		$property = "_". $type . "File";
		return $this->$property;	
	}

	/*
	 * Set the image from specified property
	 * 
	 * @param  string $type 
	 * @param  string  $img
	 * @return null
	 */
	public function setImg($type, $img)
	{
		$property = "_". $type . "Img";
		$this->$property = $img;	
	}

	/*
	 * Get the image for specified property
	 * 
	 * @return array
	 */
	public function getImg($type)
	{
		$property = "_". $type . "Img";
		return $this->$property;	
	}

	/*
	 * Set the link for specified property
	 * 
	 * @param  string $type 
	 * @param  string $path
	 * @return null
	 */
	public function setPath($type, $path)
	{
		$property = "_". $type . "Path";
		$this->$property = $path;	
	}

	/*
	 * Get the path for specified property
	 * 
	 * @return string
	 */
	public function getPath($type)
	{
		$property = "_". $type . "Path";
		return $this->$property;	
	}

	/*
	 * Set the path for specified property
	 * 
	 * @param  string $type 
	 * @param  string $link
	 * @return null
	 */
	public function setLink($type, $link)
	{
		$property = "_". $type . "Link";
		$this->$property = $link;	
	}

	/*
	 * Get the link for specified property
	 * 
	 * @return string
	 */
	public function getLink($type)
	{
		$property = "_". $type . "Link";
		return $this->$property;	
	}

	/*
	 * Get the specified properties
	 * 
	 * @param  string $type
	 * @return array
	 */
	public function getInfo($type)
	{
		$propertyImg  = "_". $type . "Img";
		$propertyLink = "_". $type . "Link";
		$propertyPath = "_". $type . "Path";

		$info = array('img'  => $this->$propertyImg, 
					  'link' => $this->$propertyLink,
					  'path' => $this->$propertyPath);

		return $info;	
	}

	/*
	 * Upload file
	 * 
	 * @param  string $type
	 */
	protected function _uploadFile($type) {
		$path = $this->getPath($type);
		$file = $this->getFile($type);
		$file_tmp_name = $file["tmp_name"];
		$file_name = $file["name"];

		move_uploaded_file($file_tmp_name, dirname(__FILE__) . "/../..$path/$file_name");
	}

	/*
	 * Set status property
	 * 
	 * @param  string $status
	 */
	public function setStatus($status)
	{
		$this->_status = $status;	
	}

	/*
	 * Get status property
	 * 
	 * @return  string
	 */
	public function getStatus()
	{
		return $this->_status;	
	}

	/*
	 * Set eBlast HTML
	 * 
	 */
	public function setEblastHtml()
	{
		$replacements = array($this->getImg('eblast'), 
							  $this->getLink('eblast'), 
							  $this->getPath('eblast'));

		$patterns 	  = array('/{{eblast_img}}/', 
						      '/{{eblast_link}}/', 
						      '/{{eblast_path}}/');

		$template = file_get_contents('eblast_template.php');
		$html = preg_replace($patterns, $replacements, $template);

		$this->_eblastHtml = $html;
	}

	/*
	 * Get eBlast HTML
	 * 
	 * @return  string
	 */
	public function getEblastHtml()
	{
		return $this->_eblastHtml;	
	}

	/*
	 * Create eBlast file for future use
	 * 
	 * @param string $directory	Path to where the file is created 
	 * @param string $html		HTML to write to the file 
	 */
	protected function _createEblastFile($directory, $html)
	{
		$handle = fopen(dirname(__FILE__) . "/../..$directory/eblast.htm", 'w') or die("Can't open eblast file");
		fwrite($handle, $html);
		fclose($handle);	
	}

	/*
	 * Create specified directory
	 * 
	 * @return  string
	 */
	protected function _createDirectory($path)
	{
		mkdir(dirname(__FILE__) . $path, 0777);
	}

	/*
	 * Create directory and return string
	 * 
	 * @param  string $name Event name
	 * @return string
	 */
	protected function _getDirectory($name)
	{
		$year = date('Y');
		$month = date('m');
		$day = date('d');

		if(empty($name)) {
			$this->setStatus("Event name was not submitted.");
			return false;
		}

		$nameUpdated = preg_replace("/[^a-zA-Z0-9\s]/", "", strtolower($name));
		$eventDir = str_replace(" ", "-", $nameUpdated);

		if(!file_exists(dirname(__FILE__) . "/../../mailings")) {
			$this->_createDirectory("/../../mailings");
		}

		if(!file_exists(dirname(__FILE__) . "/../../mailings/events")) {
			$this->_createDirectory("/../../mailings/events");
		}

		if(!file_exists(dirname(__FILE__) . "/../../mailings/events/$year")) {
			$this->_createDirectory("/../../mailings/events/$year");
		}

		if(!file_exists(dirname(__FILE__) . "/../../mailings/events/$year/$month")) {
			$this->_createDirectory("/../../mailings/events/$year/$month");
		}

		if(!file_exists(dirname(__FILE__) . "/../../mailings/events/$year/$month/$eventDir")) {
			$this->_createDirectory("/../../mailings/events/$year/$month/$eventDir");
		}

		$directory = "/mailings/events/$year/$month/$eventDir";

		return $directory;
	}

	/*
	 * Create a new event
	 * 
	 * @param string $name Event name 
	 */
	public function createEvent($name)
	{
		$eblastFile = $this->getFile('eblast');
		$bannerFile = $this->getFile('banner');

		if(empty($eblastFile) && empty($bannerFile)) {
			$message = "No images were submitted.";
			return false;
		}

		if (isset($eblastFile["type"]) && $eblastFile["type"] !== "image/jpeg" ||
			isset($bannerFile["type"]) && $bannerFile["type"] !== "image/jpeg") {
			$message = "The files uploaded must be jpeg format.";
			$this->setStatus($message);

			return false;
		} else {
			$directory = $this->_getDirectory($name);

			$this->setPath('eblast', $directory);
			$this->setPath('banner', $directory);

			if(!empty($bannerFile)) {
				$this->_uploadFile('banner');
			}

 			if(!empty($eblastFile)) {
				$this->_uploadFile('eblast');

				$this->setEblastHtml();
				$html = $this->getEblastHtml();
				$handle = $this->_createEblastFile($directory, $html);
			}

			$message = "File successfully uploaded.";
			$this->setStatus($message);
		}
	}
}
