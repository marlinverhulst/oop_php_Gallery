<?php
/**
 *
 */
class Photo extends Db_object
{

  protected static $db_table = "photos";

  protected static $db_table_fields = array('id', 'title', 'caption', 'description', 'filename', 'alternate_text', 'type', 'size');
  public $id;
  public $title;
  public $description;
  public $caption;
  public $filename;
  public $alternate_text;
  public $type;
  public $size;

  public $tmp_path;
  public $upload_directory = "images";
  public $errors = array();
  public $upload_errors_array = array(

  UPLOAD_ERR_OK => "There is no error",
  UPLOAD_ERR_INI_SIZE => "Upload file exceeds the max file size",
  UPLOAD_ERR_FORM_SIZE => "Upload file exceeds the Max_file_size direcive",
  UPLOAD_ERR_PARTIAL => "Upload interupted (not complete)",
  UPLOAD_ERR_NO_FILE => "No file uploaded",
  UPLOAD_ERR_NO_TMP_DIR => " No temp directory found",
  UPLOAD_ERR_CANT_WRITE => "Can't write to disk",
  UPLOAD_ERR_EXTENSION => "A PHP Extension stopped the upload "

  );
  // This is passing $_FILES['uploaded_file'] as an Argument
  public function set_file($file)
  {
    if(empty($file) || !$file || !is_array($file))
    {
      $this->errors[] = "There was no file uploaded ";
      return false;
    } elseif($file['error'] !=0)
      {
        $this->errors[] = $this->$upload_errors_array[$file['error']];
        return false;
      } else
        {
          // if no errors
          $this->filename = basename($file['name']);
          $this->tmp_path = $file['tmp_name'];
          $this->type = $file['type'];
          $this->size = $file['size'];
        }
      }

  public function picture_path()
  {
    return $this->upload_directory.DS.$this->filename;
  }

  public function save()
  {
    if($this->id)
    {
      $this->update();
    } else
      {
        if(!empty($this->errors))
        {
          return false;
        }
        if(empty($this->filename) || empty($this->tmp_path))
        {
          $this->errors[] = "The file was not available";
          return false;
        }
        $target_path = SITE_ROOT.DS.'admin'.DS.$this->upload_directory.DS.$this->filename;

        if(file_exists($target_path))
        {
          $this->errors[] = "The file {$this->filename} already exists";
          return false;
        }
        if (move_uploaded_file($this->tmp_path, $target_path))
        {
          if($this->create())
          {
            unset($this->tmp_path);
            return true;
          }

        }
        else
        {
          $this->errors[] = "The file-directory has no write permission";
          return false;
        }

      }

  }

  public function delete_photo()
  {
    if ($this->delete())
    {
      $target_path = SITE_ROOT.DS.'admin'.DS.$this->picture_path();

      return unlink($target_path) ? true : false;

    }
     else
    {
      return false;
    }
  }

public static function display_sidebar_data($photo_id)
{
  $photo = Photo::find_by_id($photo_id);

  $output = "<a class='thumbnail' href='#'><img width='100' src='{$photo->picture_path()}'";
  $output .= "<p>{$photo->filename}</p>";
  $output .="<p>{$photo->type}</p>";
  $output .="<p>{$photo->size}</p>";

  echo $output;
}

// CLASS END




}




 ?>
