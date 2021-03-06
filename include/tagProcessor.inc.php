<?php

//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant                            |
//  | http://www.ompd.pl                                                     |
//  |                                                                        |
//  |                                                                        |
//  | netjukebox, Copyright © 2001-2012 Willem Bartels                       |
//  |                                                                        |
//  | http://www.netjukebox.nl                                               |
//  | http://forum.netjukebox.nl                                             |
//  |                                                                        |
//  | This program is free software: you can redistribute it and/or modify   |
//  | it under the terms of the GNU General Public License as published by   |
//  | the Free Software Foundation, either version 3 of the License, or      |
//  | (at your option) any later version.                                    |
//  |                                                                        |
//  | This program is distributed in the hope that it will be useful,        |
//  | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
//  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
//  | GNU General Public License for more details.                           |
//  |                                                                        |
//  | You should have received a copy of the GNU General Public License      |
//  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
//  +------------------------------------------------------------------------+


//  +------------------------------------------------------------------------+
//  | tag properties                                                         |
//  +------------------------------------------------------------------------+
function parseTrackArtist($data) {
    if (isset($data['comments']['artist'][0])) {
        return $data['comments']['artist'][0];
    }
    return 'Unknown TrackArtist';
}

function parseTrackTitle($data) {
    if(isset($data['comments']['title'][0])) {
        return $data['comments']['title'][0];
    }
    return 'Unknown Title';
}

function parseGenre($data) {
    if (isset($data['comments']['genre'][0])) {
        return $data['comments']['genre'][0];
    }
    return 'Unknown Genre';
}

function parseTrackNumber($data) {
    if (isset($data['comments']['track'][0])) {
        return postProcessTrackNumber($data['comments']['track'][0]);
    }
    if (isset($data['comments']['tracknumber'][0])) {
        return postProcessTrackNumber($data['comments']['tracknumber'][0]);
    }
    if (isset($data['comments']['track_number'][0])) {
        return postProcessTrackNumber($data['comments']['track_number'][0]);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}

function postProcessTrackNumber($numberString) {
    //support for track_number in form: 01/11
    $numbers = explode("/", $numberString);
    return $numbers[0];
}

function parseYear($data) {
    if (isset($data['comments']['year'][0])) {
        return postProcessYear($data['comments']['year'][0]);
    }
    if (isset($data['comments']['date'][0])) {
        return postProcessYear($data['comments']['date'][0]);
    }
    if (isset($data['comments']['creation_date'][0])) {
        return postProcessYear($data['comments']['creation_date'][0]);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}

function postProcessYear($yearString) {
    if (preg_match('#[1][9][0-9]{2}|[2][0-9]{3}#', $yearString, $match)) {
        $yearString = $match[0];
    }
    return intval($yearString);
}

function parseComment($data) {
    if(isset($data['comments']['comment']) === FALSE) {
        return '';
    }
    if(is_array($data['comments']['comment']) === FALSE) {
        return '';
    }
    $commentsArray = array_values($data['comments']['comment']);
    if(isset($commentsArray[0])) {
        return $commentsArray[0];
    }
    return '';
}

// TODO: this function is currently not used but removed from old fileInfo() code-mess
// consider to make use of it within fileStructure() or whereelse needed
function parseAlbumArtist($data) {
    if (isset($data['comments']['albumartist'][0])) {
        return $data['comments']['albumartist'][0];
    }
    if (isset($data['comments']['band'][0])) {
        return $data['comments']['band'][0];
    }
    return 'Unknown AlbumArtist';
}




function parseMimeType($data) {
    if(isset($data['mime_type'])) {
        return $data['mime_type'];
    }
    return 'application/octet-stream';
}

function parseError($data) {
    if (isset($data['error'])) {
        return implode('<br>', $data['error']);
    }
    return '';
}

//  +------------------------------------------------------------------------+
//  | audio tech properties                                                  |
//  +------------------------------------------------------------------------+
function parseMiliseconds($data) {
    if(isset($data['playtime_seconds'])) {
        return round($data['playtime_seconds'] * 1000);
    }
    return 0;
}

function parseAudioBitRate($data) {
    if(isset($data['audio']['bitrate'])) {
        return round($data['audio']['bitrate']); // integer in database
    }
    return 0;
}

function parseAudioBitRateMode($data) {
    if(isset($data['audio']['bitrate_mode'])) {
        return $data['audio']['bitrate_mode'];
    }
    return '';
}

function parseAudioBitsPerSample($data) {
    if(isset($data['audio']['bits_per_sample'])) {
        return $data['audio']['bits_per_sample'];
    }
    return 16;
}

function parseAudioSampleRate($data) {
    if(isset($data['audio']['sample_rate'])) {
        return $data['audio']['sample_rate'];
    }
    return 44100;
}

function parseAudioChannels($data) {
    if(isset($data['audio']['channels'])) {
        return $data['audio']['channels'];
    }
    return 2;
}

function parseAudioLossless($data) {
    if(empty($data['audio']['lossless']) == false) {
        return 1;
    }
    return 0;
}

function parseAudioCompressionRatio($data) {
    if(isset($data['audio']['compression_ratio'])) {
        return $data['audio']['compression_ratio'];
    }
    return 0;
}

function parseAudioDataformat($data) {
    if(isset($data['audio']['dataformat'])) {
        return $data['audio']['dataformat'];
    }
    return '';
}

function parseAudioEncoder($data) {
    if(isset($data['audio']['encoder'])) {
        return $data['audio']['encoder'];
    }
    return 'Unknown encoder';
}

function parseAudioProfile($data) {
    if(parseAudioLossless($data) === 1) {
        return (parseAudioCompressionRatio($data) == 1)
            ? 'Lossless'
            : 'Lossless compression';
    }
    if(isset($data['aac']['header']['profile_text'])) {
        return $data['aac']['header']['profile_text'];
    }
    if(isset($data['mpc']['header']['profile'])) {
        return $data['mpc']['header']['profile'];
    }
    return parseAudioBitRateMode($data) . ' ' . round(parseAudioBitRate($data) / 1000, 1) . ' kbps';
}

function parseAudioDynamicRange($data) {
    if(isset($data['comments']['dynamic range'][0])) {
        return intval($data['comments']['dynamic range'][0]);
    }
    if(isset($data['tags']['id3v2']['text']['DYNAMIC RANGE'])) {
        return intval($data['tags']['id3v2']['text']['DYNAMIC RANGE']);
    }
    // TODO: handling NULL-values when building the query. for now return a string
    return 'NULL';
}


//  +------------------------------------------------------------------------+
//  | video tech properties                                                  |
//  +------------------------------------------------------------------------+
function parseVideoDataformat($data) {
    if(isset($data['video']['dataformat'])) {
        return $data['video']['dataformat'];
    }
    return '';
}

function parseVideoCodec($data) {
    if(isset($data['video']['codec'])) {
        return $data['video']['codec'];
    }
    return 'Unknown codec';
}

function parseVideoResolutionX($data) {
    if(isset($data['video']['resolution_x'])) {
        return intval($data['video']['resolution_x']);
    }
    return 0;
}

function parseVideoResolutionY($data) {
    if(isset($data['video']['resolution_y'])) {
        return intval($data['video']['resolution_y']);
    }
    return 0;
}

function parseVideoFrameRate($data) {
    if(isset($data['video']['frame_rate'])) {
        return intval($data['video']['frame_rate']);
    }
    return 0;
}

