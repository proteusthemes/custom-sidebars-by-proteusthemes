#!/bin/bash -ex
#
# Exist codes:
# - 0: OK
# - 1: file missing
# - 2: folder missing

function testRequiredFiles {
	parentFolder='./'
	requiredFiles=(
		custom-sidebars-by-proteusthemes.php
		readme.txt
		assets/css/main.min.css
		assets/js/main.min.js
		bower_components/tinyscrollbar/lib/jquery.tinyscrollbar.min.js
		inc/views/metabox.php
		inc/views/widgets-delete.php
		inc/views/widgets-editor.php
		inc/views/widgets.php
		inc/class-pt-cs-editor.php
		inc/class-pt-cs-main.php
		inc/class-pt-cs-replacer.php
		inc/class-pt-cs-widgets.php
	)
	requiredFolders=(
		assets/js
		assets/css
		inc
		languages
	)

	# loop for files
	for file in "${requiredFiles[@]}"
	do
		filePath="$parentFolder/$file"
		if [[ ! -f $filePath ]]; then
			echo "File $filePath does not exist!"
			exit 1
		fi
	done

	# loop for directories
	for folder in "${requiredFolders[@]}"
	do
		folderPath="$parentFolder/$folder"
		if [[ ! -d $folderPath ]]; then
			echo "Directory $folderPath does not exist!"
			exit 2
		fi
	done
}

# call and unset
testRequiredFiles
unset testRequiredFiles