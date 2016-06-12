# plexPlaylistToWaltr

Walter does not generate a thumbnail image for movies.

Install:

1. copy script.ini.template to script.ini
2. Populate the location of the Plex server (if you run it on the same machine than no need to change that from the template)
3. Populate the location you want the movies to be copied to (make sure you have enough room there).

Workflow:

1. Create a new playlist of the movies you would like to get on your iOS device.
2. Add the movies you would like to copy to the playlist.
3. Run the script php thumbnailMovies.php "<play list name>"

This will copy the movies together with the thumbnails to the destination you chose.
All you need to do now is open waltr and copy the folder to your iOS device.
