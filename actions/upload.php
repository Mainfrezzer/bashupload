<?php

# Upload file(s) handler



# First, let's check raw input data
if ( $f = fopen('php://input', 'r') )
{
	$name = rawurldecode(trim($uri, '/'));
	if ( !$name ) $name = uniqid();
	$tmp = tempnam('/app/files/tmp', 'upload');

	$ftmp = fopen($tmp, 'w');
	while ( !feof($f) ) fputs($ftmp, fgets($f));

	fclose($f);
	fclose($ftmp);

	if ( filesize($tmp) ) $_FILES[] = [
		'tmp_name' => $tmp,
		'name' => $name
	];
}



# Next, let's move uploaded files to the storage
$id = gen_id();
foreach ( $_FILES as $key_file => $file )
{
# make file name safe
$file['name'] = trim($file['name'], '/');
$file['name'] = str_replace(['/', '\\'], '_', $file['name']);

# Only generate a new name if the filename is extremely long
# Keep the original extension when renaming.
if (strlen($file['name']) > 255) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    $file['name'] = gen_id();

    if ($extension !== '') {
        $file['name'] .= '.' . $extension;
    }
}

# move file to a final location
$destination = STORAGE . '/' . md5('/' . $id . '-' . $file['name']);
rename($file['tmp_name'], $destination);

# register this uploaded file data
$uploads[] = [
    'id' => (isset($rewrite_id) ? : $id),
    'name' => $file['name'],
    'path' => $destination,
    'size' => filesize($destination),
    'upload_name' => $key_file,
    'is_rewritten' => isset($rewrite_id) ? true : false
];

}
