/*
 * File: inc/vector-uploads.php
 * Description: Enables upload of vector graphics formats (EPS, AI, SVG, etc.)
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-10-02
 */

add_action('after_setup_theme', function () {
    $GLOBALS['bt_vector_mimes'] = [
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
        'pdf'  => 'application/pdf',
        'cdr'  => 'application/vnd.corel-draw',
        'cmx'  => 'image/x-cmx',
        'fh'   => 'application/vnd.adobe.freehand',
        'fh7'  => 'application/vnd.adobe.freehand',
        'fh8'  => 'application/vnd.adobe.freehand',
        'fh9'  => 'application/vnd.adobe.freehand',
        'fh10' => 'application/vnd.adobe.freehand',
        'fh11' => 'application/vnd.adobe.freehand',
        'wpg'  => 'application/x-wpg',
        'xar'  => 'application/vnd.xara',
        'fig'  => 'application/x-xfig',
        'drw'  => 'image/x-drw',
        'dxf'  => 'application/dxf',
        'dwg'  => 'application/acad',
        'dgn'  => 'application/vnd.microstation-dgn',
        'igs'  => 'model/iges',
        'iges' => 'model/iges',
        'cgm'  => 'image/cgm',
        'hpgl' => 'application/vnd.hp-hpgl',
        'plt'  => 'application/vnd.hp-hpgl',
        'wmf'  => 'image/wmf',
        'emf'  => 'image/emf',
        'vsd'  => 'application/vnd.visio',
        'vsdx' => 'application/vnd.ms-visio.drawing.main+xml',
        'stl'  => 'model/stl',
        'amf'  => 'model/amf',
        'skp'  => 'application/vnd.sketchup.skp',
        'sxd'  => 'application/vnd.sun.xml.draw',
        'odg'  => 'application/vnd.oasis.opendocument.graphics',
    ];
});

add_filter('upload_mimes', function ($mimes) {
    if (!isset($GLOBALS['bt_vector_mimes'])) return $mimes;
    foreach ($GLOBALS['bt_vector_mimes'] as $ext => $mime) {
        $mimes[$ext] = $mime;
    }
    return $mimes;
}, 10, 1);

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes, $real_mime) {
    if (!isset($GLOBALS['bt_vector_mimes'])) return $data;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (isset($GLOBALS['bt_vector_mimes'][$ext])) {
        $data['ext']  = $ext;
        $data['type'] = $GLOBALS['bt_vector_mimes'][$ext];
        if (empty($data['proper_filename'])) {
            $data['proper_filename'] = $filename;
        }
    }
    return $data;
}, 10, 5);
