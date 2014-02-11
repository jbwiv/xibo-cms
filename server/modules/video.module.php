<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2006,2007,2008 Daniel Garner and James Packer
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */
class video extends Module
{
    // Custom Media information
    protected $maxFileSize;
    protected $maxFileSizeBytes;

    public function __construct(database $db, user $user, $mediaid = '', $layoutid = '', $regionid = '', $lkid = '')
    {
        // Must set the type of the class
        $this->type= 'video';
        $this->displayType = __('Video');

        // Get the max upload size from PHP
        $this->maxFileSize 	= ini_get('upload_max_filesize');
        $this->maxFileSizeBytes = convertBytes($this->maxFileSize);

        // Must call the parent class
        parent::__construct($db, $user, $mediaid, $layoutid, $regionid, $lkid);
    }

    /**
     * Sets the Layout and Region Information
     *  it will then fill in any blanks it has about this media if it can
     * @return
     * @param $layoutid Object
     * @param $regionid Object
     * @param $mediaid Object
     */
    public function SetRegionInformation($layoutid, $regionid)
    {
        $db =& $this->db;
        $this->layoutid = $layoutid;
        $this->regionid = $regionid;
        $mediaid = $this->mediaid;
        $this->existingMedia = false;

        if ($this->regionSpecific == 1) return;

        // Load what we know about this media into the object
        $SQL = "SELECT storedAs FROM media WHERE mediaID = $mediaid ";

        if (!$row= $db->GetSingleRow($SQL))
        {
            trigger_error($db->error());
            return false;
        }

        $storedAs   = $row['storedAs'];

        // Any Options
        $this->SetOption('uri', $storedAs);

        return true;
    }

    /**
     * Return the Add Form as HTML
     * @return
     */
    public function AddForm()
    {
        return $this->AddFormForLibraryMedia();
    }

    /**
     * Return the Edit Form as HTML
     * @return
     */
    public function EditForm()
    {
        return $this->EditFormForLibraryMedia();
    }

    /**
     * Add Media to the Database
     * @return
     */
    public function AddMedia()
    {
        return $this->AddLibraryMedia();
    }

    /**
     * Edit Media in the Database
     * @return
     */
    public function EditMedia()
    {
        return $this->EditLibraryMedia();
    }
    
    /**
     * Get Resource
     */
    public function GetResource($displayId = 0)
    {
    	// Return the raw flash file with appropriate headers
    	$library = Config::GetSetting("LIBRARY_LOCATION");
        $fileName = $library . $this->GetOption('uri', '');

        if (!$video = file_get_contents($fileName))
        {
            //not sure
            Debug::LogEntry('audit', "File not found: " . $this->GetOption('uri',''), 'video', 'GetResource');

            die ('File not found');
        }

        $size = strlen($video);
        $fi = new finfo( FILEINFO_MIME_TYPE );
        $mime = $fi->file( $fileName );

        //Output a header
        header("Content-Type: {$mime}");
        header('Pragma: public');
        header('Cache-Control: max-age=86400');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Length: ' . $size);
        
        return $video;
    	
    }
}
?>
