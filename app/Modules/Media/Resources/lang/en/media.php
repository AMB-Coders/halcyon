<?php
return [
	'module name' => 'Media Manager',
	'module desc' => 'Module for managing site media',

	'ALIGN' => 'Align',
	'ALIGN_DESC' => 'If "Not Set", the alignment is defined by the class ".img_caption.none". Usually to get the image centred on the page.',
	'BROWSE_FILES' => 'Browse files',
	'CAPTION' => 'Caption',
	'CAPTION_DESC' => 'If set to "Yes", the Image Title will be used as caption.',
	'CLEAR_LIST' => 'Clear List',
	'CONFIGURATION' => 'Media Manager Options',
	'CREATE_COMPLETE' => 'Create Complete: %s',
	'create folder' => 'Create Folder',
	//'CREATE' => 'Create',

	'folder' => 'Folder',
	'folder name' => 'Folder name',
	'folder path' => 'Folder path',
	'rename' => 'Rename',
	//'DIRECTORY' => 'Directory',
	//'DIRECTORY_UP' => 'Directory Up',

	'CURRENT_PROGRESS' => 'Current progress',
	'DELETE_COMPLETE' => 'Delete Complete: %s',
	'DESCFTPTITLE' => 'FTP Login Details',
	'DESCFTP' => 'To upload, change and delete media files, the CMS will most likely need your FTP account details. Please enter them in the form fields below.',
	'DETAIL_VIEW' => 'List View',

	// Error messages
	'ERROR_BAD_REQUEST' => 'Bad Request',
	'ERROR_BEFORE_DELETE_0' => 'Some error occurs before deleting the media',
	'ERROR_BEFORE_DELETE_1' => 'An error occurs before deleting the media: %s',
	'ERROR_BEFORE_DELETE_MORE' => 'Some errors occur before deleting the media: %s',
	'ERROR_BEFORE_SAVE_0' => 'Some error occurs before saving the media',
	'ERROR_BEFORE_SAVE_1' => 'An error occurs before saving the media: %s',
	'ERROR_BEFORE_SAVE_MORE' => 'Some errors occur before saving the media: %s',
	'ERROR_CREATE_NOT_PERMITTED' => 'Create not permitted',
	'ERROR_FILE_EXISTS' => 'File already exists',
	'ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME' => 'Unable to create directory. Directory name must only contain alphanumeric characters and no spaces.',
	'ERROR_UNABLE_TO_BROWSE_FOLDER_WARNDIRNAME' => 'Unable to browse:&#160;%s. Directory name must only contain alphanumeric characters and no spaces.',
	'ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME' => 'Unable to delete:&#160;%s. File name must only contain alphanumeric characters and no spaces.',
	'ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY' => 'Unable to delete:&#160;%s. Folder is not empty!',
	'ERROR_UNABLE_TO_DELETE_FOLDER_WARNDIRNAME' => 'Unable to delete:&#160;%s. Directory name must only contain alphanumeric characters and no spaces.',
	'ERROR_UNABLE_TO_DELETE' => ' Unable to delete:&#160;',
	'ERROR_UNABLE_TO_UPLOAD_FILE' => 'Unable to upload file.',
	'ERROR_UPLOAD_INPUT' => 'Please input a file for upload',
	'ERROR_WARNFILENAME' => 'File name must only contain alphanumeric characters and no spaces.',
	'ERROR_WARNFILETOOLARGE' => 'This file is too large to upload.',
	'ERROR_WARNFILETYPE' => 'This file type is not supported.',
	'ERROR_WARNIEXSS' => 'Possible IE XSS Attack found.',
	'ERROR_WARNINVALID_IMG' => 'Not a valid image.',
	'ERROR_WARNINVALID_MIME' => 'Illegal or invalid mime type detected.',
	'ERROR_WARNNOTADMIN' => 'Uploaded file is not an image file and you are not a manager or higher.',
	'ERROR_WARNNOTEMPTY' => 'Not empty!',

	// Fields
	'FIELD_CHECK_MIME_DESC' => 'Use MIME Magic or Fileinfo to attempt to verify files. Try disabling this if you get invalid mime type errors',
	'FIELD_CHECK_MIME_LABEL' => 'Check MIME Types',
	'FIELD_ENABLE_FLASH_UPLOADER_DESC' => 'Flash uploader lets upload multiple files at the same time. It may not work on your settings',
	'FIELD_ENABLE_FLASH_UPLOADER_LABEL' => 'Enable flash uploader',
	'FIELD_IGNORED_EXTENSIONS_DESC' => 'Ignored file extensions for MIME type checking and restricted uploads',
	'FIELD_IGNORED_EXTENSIONS_LABEL' => 'Ignored Extensions',
	'FIELD_ILLEGAL_MIME_TYPES_DESC' => 'A comma separated list of illegal MIME types for upload (blacklist)',
	'FIELD_ILLEGAL_MIME_TYPES_LABEL' => 'Illegal MIME Types',
	'FIELD_LEGAL_EXTENSIONS_DESC' => ' Extensions (file types) you are allowed to upload (comma separated).',
	'FIELD_LEGAL_EXTENSIONS_LABEL' => 'Legal Extensions (File Types)',
	'FIELD_LEGAL_IMAGE_EXTENSIONS_DESC' => ' Image Extensions (file types) you are allowed to upload (comma separated). These are used to check for valid image headers.',
	'FIELD_LEGAL_IMAGE_EXTENSIONS_LABEL' => 'Legal Image Extensions (File Types)',
	'FIELD_LEGAL_MIME_TYPES_DESC' => 'A comma separated list of legal MIME types for upload',
	'FIELD_LEGAL_MIME_TYPES_LABEL' => 'Legal MIME Types',
	'FIELD_MAXIMUM_SIZE_DESC' => 'The maximum size for an upload (in megabytes). Use zero for no limit. Note: your server has a maximum limit.',
	'FIELD_MAXIMUM_SIZE_LABEL' => 'Maximum Size (in MB)',
	'FIELD_PATH_FILE_FOLDER_DESC' => 'Enter the path to the files folder relative to root. Warning! Changing to another path than the default "images" may break your links.',
	'FIELD_PATH_FILE_FOLDER_LABEL' => 'Path to files folder',
	'FIELD_PATH_IMAGE_FOLDER_DESC' => 'Enter the path to the images folder relative to root. This path <strong>has to be the same as path to files (default) or to a subfolder of the path to file folder.</strong>',
	'FIELD_PATH_IMAGE_FOLDER_LABEL' => 'Path to images folder',
	'FIELD_RESTRICT_UPLOADS_DESC' => 'Restrict uploads for lower than manager users to just images if Fileinfo or MIME Magic isn\'t installed.',
	'FIELD_RESTRICT_UPLOADS_LABEL' => 'Restrict Uploads',
	'FILES' => 'Files',

	// File size
	'filesize' => 'File size',
	'filesize bytes' => ':size B',
	'filesize kilobytes' => ':size KB',
	'filesize megabytes' => ':size MB',

	// Misc
	'list' => [
		'name' => 'Name',
		'size' => 'Size',
		'type' => 'Type',
		'modified' => 'Last modified',
		'path' => 'Path',
		'width' => 'Width',
		'height' => 'Height',
	],

	//'FOLDERS' => 'Media Folders',
	//'FOLDERS_PATH_LABEL' => 'Changing the default "path to files folder" to another folder than default "images" may break your links.<br /> The "path to images" folder has to be the same or to a subfolder of "path to files".',
	//'IMAGE_DESCRIPTION' => 'Image Description',
	'image title' => ':name - :size',
	//'image dimensions' => '%1$s x %2$s',
	//'IMAGE_URL' => 'Image URL',
	//'INSERT_IMAGE' => 'Insert Image',
	//'INSERT' => 'Insert',
	//'INVALID_REQUEST' => 'Invalid Request',
	//'MEDIA' => 'Media',
	//'NAME' => 'Image Name',
	'upload' => 'Upload',
	'download' => 'Download',
	'file info' => 'Info',
	'file link' => 'Get link',
	'file path' => 'File path',
	'NO_IMAGES_FOUND' => 'No Images Found',
	'NOT_SET' => 'Not Set',
	'OVERALL_PROGRESS' => 'Overall Progress',
	'PIXEL_DIMENSIONS' => 'Pixel Dimensions (W x H)',
	'START_UPLOAD' => 'Start Upload',
	'THUMBNAIL_VIEW' => 'Thumbnail View',
	'TITLE' => 'Image Title',
	'UPLOAD_COMPLETE' => 'Upload Complete: :file',
	'UPLOAD_FILES_NOLIMIT' => 'Upload files (No maximum size)',
	'UPLOAD_FILES' => 'Upload files (Maximum Size: :size MB)',
	'UPLOAD_FILE' => 'Upload file',
	'UPLOAD_SUCCESSFUL' => 'Upload Successful',
	'UPLOAD_INSTRUCTIONS' => 'Drop files or click here to upload',
	'UPLOAD_INSTRUCTIONS_BTN' => 'Drop files on the file list or click here to upload',
	'UP' => 'Up',
];
