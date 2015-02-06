<?php

//格式化文件大小 以KB MB GB为单位
function filesize_format($size) {

    if ($size / 1024 >= 1) {
        $size = sprintf("%.2f", $size / 1024);

        if ($size / 1024 >= 1) {
            $size = sprintf("%.2f", $size / 1024);
            if ($size / 1024 >= 1) {
                $size = sprintf("%.2f", $size / 1024);
                return $size . 'GB';
            } else {
                return $size . 'MB';
            }
        } else {
            return $size . 'KB';
        }
    } else {
        return $size . 'B';
    }
}

function mv($oldname,$newname){
    if (!is_file($oldname))
        return error('oldfile '.$oldname.' missed');
    if (is_file($newname))
        return error('newfile '.$newname.' exists');
    return rename($oldname, $newname);
}

function del($filename){
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $r = @unlink($filename);
    if (is_file($filename))
        return error('delete file '.$filename.' failed');
    return $r;
}

function writeline($filename,$string) {
    if (!writable($filename))
        return false;
    $string = (string)$string;
    if (empty($string))
        return error('input string is empty');
    $file = fopen($filename, 'a+b');
    fwrite($file, $string . "\r\n");
    fclose($file);
    return true;
}

function clear($filename){
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $file = fopen($filename, 'w+b');
    fclose($file);
    return true;
}

function tmp($prefix = 'tmp', $dir = '/tmp') {
    $filename = tempnam($dir, $prefix);
    if (!$filename)
        return error('cannot create template file');
    if (!readable($filename))
        return false;
    return realpath($filename);
}

function download($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $filesize = filesize($filename);
    $file = fopen($filename, 'rb');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . urlencode($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $filesize);
    ob_clean();
    flush();
    echo fread($file, $filesize);
}

function readable($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    if (is_readable($filename))
        return true;
    chmod($filename, 644);
    return is_readable($filename)?true:!error('[file\readable] file '.$filename.' is unreadable');
}

function writable($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    if (is_writable($filename))
        return true;
    chmod($filename, 777);
    return is_writable($filename)?true:!error('[file\writable] file '.$filename.' is unwritable');
}

//获取不带扩展名的文件名
function fileprename($filename){
	return basename($filename,'.'.pathinfo($filename,PATHINFO_EXTENSION));
}

//获取文件扩展名
function fileext($filename,$check = false){
    if (is_file($filename)) {
        if ($check)
            return pathinfo($filename,PATHINFO_EXTENSION);
        $file = fopen($filename, "rb");
        $bin = fread($file, 2); //只读2字节  
        fclose($file);
    } else {
        $bin = substr($filename, 0, 2);
    }
    $strInfo = @unpack("C2chars", $bin);
    $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
    $fileType = '';
    switch ($typeCode) {
        case 7790:
            $fileType = 'exe';
            break;
        case 7784:
            $fileType = 'midi';
            break;
        case 8297:
            $fileType = 'rar';
            break;
        case 8075:
            $fileType = 'zip';
            break;
        case 255216:
            $fileType = 'jpg';
            break;
        case 7173:
            $fileType = 'gif';
            break;
        case 6677:
            $fileType = 'bmp';
            break;
        case 13780:
            $fileType = 'png';
            break;
        default:
            $fileType = '';
    }

    //Fix  
    if ($strInfo['chars1'] == '-1' AND $strInfo['chars2'] == '-40')
        return 'jpg';
    if ($strInfo['chars1'] == '-119' AND $strInfo['chars2'] == '80')
        return 'png';

    return $fileType;
}

//根据mime获取扩展名
function mime2ext($mime){
    $mimes = array (
        'application/internet-property-stream' => 'acx',
        'application/postscript' => 'ai eps ps',
        'audio/x-aiff' => 'aif aifc aiff',
        'video/x-ms-asf' => 'asf asr asx lsf lsx',
        'audio/basic' => 'au snd',
        'video/x-msvideo' => 'avi',
        'application/olescript' => 'axs',
        'text/plain' => 'bas c h txt asc',
        'application/x-bcpio' => 'bcpio',
        'application/octet-stream' => 'bin class dms exe lha lzh ani avb bpk cur dll dmg ico tad ttf',
        'image/bmp' => 'bmp',
        'application/vnd.ms-pkiseccat' => 'cat',
        'application/x-cdf' => 'cdf',
        'application/x-x509-ca-cert' => 'cer crt der',
        'application/x-msclip' => 'clp',
        'image/x-cmx' => 'cmx',
        'image/cis-cod' => 'cod',
        'application/x-cpio' => 'cpio',
        'application/x-mscardfile' => 'crd',
        'application/pkix-crl' => 'crl',
        'application/x-csh' => 'csh',
        'text/css' => 'css',
        'application/x-director' => 'dcr dir dxr',
        'application/x-msdownload' => 'dll',
        'application/msword' => 'doc dot',
        'application/x-dvi' => 'dvi',
        'text/x-setext' => 'etx',
        'application/envoy' => 'evy',
        'application/fractals' => 'fif',
        'x-world/x-vrml' => 'flr vrml wrl wrz xaf xof',
        'image/gif' => 'gif ifm',
        'application/x-gtar' => 'gtar',
        'application/x-gzip' => 'gz x-gzip',
        'application/x-hdf' => 'hdf',
        'application/winhlp' => 'hlp',
        'application/mac-binhex40' => 'hqx',
        'application/hta' => 'hta',
        'text/x-component' => 'htc',
        'text/html' => 'htm html stm dhtml hts',
        'text/webviewhtml' => 'htt',
        'image/x-icon' => 'ico',
        'image/ief' => 'ief',
        'application/x-iphone' => 'iii',
        'application/x-internet-signup' => 'ins isp',
        'image/pipeg' => 'jfif',
        'image/jpeg' => 'jpe jpeg jpg jpz',
        'image/pjpeg' => 'jpg',
        'application/x-javascript' => 'js',
        'application/x-latex' => 'latex',
        'video/x-la-asf' => 'lsf lsx',
        'application/x-msmediaview' => 'm13 m14 mvb',
        'audio/x-mpegurl' => 'm3u m3url',
        'application/x-troff-man' => 'man',
        'application/x-msaccess' => 'mdb',
        'application/x-troff-me' => 'me',
        'message/rfc822' => 'mht mhtml nws',
        'audio/mid' => 'mid rmi',
        'application/x-msmoney' => 'mny',
        'video/quicktime' => 'mov qt',
        'video/x-sgi-movie' => 'movie',
        'video/mpeg' => 'mp2 mpa mpe mpeg mpg mpv2',
        'audio/mpeg' => 'mp3 mpga',
        'application/vnd.ms-project' => 'mpp',
        'application/x-troff-ms' => 'ms',
        'application/oda' => 'oda',
        'application/pkcs10' => 'p10',
        'application/x-pkcs12' => 'p12 pfx',
        'application/x-pkcs7-certificates' => 'p7b spc',
        'application/x-pkcs7-mime' => 'p7c p7m',
        'application/x-pkcs7-certreqresp' => 'p7r',
        'application/x-pkcs7-signature' => 'p7s',
        'image/x-portable-bitmap' => 'pbm',
        'application/pdf' => 'pdf',
        'image/x-portable-graymap' => 'pgm',
        'application/ynd.ms-pkipko' => 'pko',
        'application/x-perfmon' => 'pma pmc pml pmr pmw',
        'image/x-png' => 'png',
        'image/x-portable-anymap' => 'pnm',
        'application/vnd.ms-powerpoint' => 'pot, pps ppt pot',
        'image/x-portable-pixmap' => 'ppm',
        'application/pics-rules' => 'prf',
        'application/x-mspublisher' => 'pub',
        'audio/x-pn-realaudio' => 'ra ram rm rmm rmvb',
        'image/x-cmu-raster' => 'ras',
        'image/x-rgb' => 'rgb',
        'application/x-troff' => 'roff t tr',
        'application/rtf' => 'rtf',
        'text/richtext' => 'rtx',
        'application/x-msschedule' => 'scd',
        'text/scriptlet' => 'sct',
        'application/set-payment-initiation' => 'setpay',
        'application/set-registration-initiation' => 'setreg',
        'application/x-sh' => 'sh',
        'application/x-shar' => 'shar',
        'application/x-stuffit' => 'sit sea',
        'application/futuresplash' => 'spl',
        'application/x-wais-source' => 'src',
        'application/vnd.ms-pkicertstore' => 'sst',
        'application/vnd.ms-pkistl' => 'stl',
        'application/x-sv4cpio' => 'sv4cpio',
        'application/x-sv4crc' => 'sv4crc',
        'application/x-tar' => 'tar taz tgz',
        'application/x-tcl' => 'tcl',
        'application/x-tex' => 'tex',
        'application/x-texinfo' => 'texi texinfo',
        'application/x-compressed' => 'tgz',
        'image/tiff' => 'tif tiff',
        'application/x-msterminal' => 'trm',
        'text/tab-separated-values' => 'tsv',
        'text/iuls' => 'uls',
        'application/x-ustar' => 'ustar',
        'text/x-vcard' => 'vcf',
        'audio/x-wav' => 'wav',
        'application/vnd.ms-works' => 'wcm wdb wks wps',
        'application/x-msmetafile' => 'wmf',
        'application/x-mswrite' => 'wri',
        'image/x-xbitmap' => 'xbm',
        'application/vnd.ms-excel' => 'xla xlc xlm xls xlt xlw',
        'image/x-xpixmap' => 'xpm',
        'image/x-xwindowdump' => 'xwd',
        'application/x-compress' => 'z',
        'application/zip' => 'zip nar',
        'application/x-zip-compressed' => 'zip',
        'application/vnd.lotus-1-2-3' => '123',
        'video/3gpp' => '3gp',
        'application/x-authoware-bin' => 'aab',
        'application/x-authoware-map' => 'aam',
        'application/x-authoware-seg' => 'aas',
        'audio/X-Alpha5' => 'als',
        'application/x-mpeg' => 'amc',
        'application/astound' => 'asd asn',
        'application/x-asap' => 'asp',
        'audio/amr-wb' => 'awb',
        'application/bld' => 'bld',
        'application/bld2' => 'bld2',
        'application/x-MS-bmp' => 'bmp',
        'application/x-bzip2' => 'bz2',
        'image/x-cals' => 'cal mil',
        'application/x-cnc' => 'ccn',
        'application/x-cocoa' => 'cco',
        'application/x-netcdf' => 'cdf nc',
        'magnus-internal/cgi' => 'cgi',
        'application/x-chat' => 'chat',
        'application/x-cmx' => 'cmx',
        'application/x-cult3d-object' => 'co',
        'application/mac-compactpro' => 'cpt',
        'chemical/x-csml' => 'csm csml',
        'x-lml/x-evm' => 'dcm evm',
        'image/x-dcx' => 'dcx',
        'application/x-dot' => 'dot',
        'drawing/x-dwf' => 'dwf',
        'application/x-autocad' => 'dwg dxf',
        'application/x-expandedbook' => 'ebk',
        'chemical/x-embl-dl-nucleotide' => 'emb embl',
        'image/x-eri' => 'eri',
        'audio/echospeech' => 'es esl',
        'application/x-earthtime' => 'etc',
        'application/x-envoy' => 'evy',
        'image/x-freehand' => 'fh4 fh5 fhc',
        'image/fif' => 'fif',
        'application/x-maker' => 'fm',
        'image/x-fpx' => 'fpx',
        'video/isivideo' => 'fvi',
        'chemical/x-gaussian-input' => 'gau',
        'application/x-gca-compressed' => 'gca',
        'x-lml/x-gdb' => 'gdb',
        'application/x-gps' => 'gps',
        'text/x-hdml' => 'hdm hdml',
        'x-conference/x-cooltalk' => 'ice',
        'image/ifs' => 'ifs',
        'audio/melody' => 'imy',
        'application/x-NET-Install' => 'ins',
        'application/x-ipscript' => 'ips',
        'application/x-ipix' => 'ipx',
        'audio/x-mod' => 'it itz m15 mdz mod s3m s3z stm ult xm xmz',
        'i-world/i-vrml' => 'ivr',
        'image/j2k' => 'j2k',
        'text/vnd.sun.j2me.app-descriptor' => 'jad',
        'application/x-jam' => 'jam',
        'application/java-archive' => 'jar',
        'application/x-java-jnlp-file' => 'jnlp',
        'application/jwc' => 'jwc',
        'application/x-kjx' => 'kjx',
        'x-lml/x-lak' => 'lak',
        'application/fastman' => 'lcc',
        'application/x-digitalloca' => 'lcl lcr',
        'application/lgh' => 'lgh',
        'x-lml/x-lml' => 'lml',
        'x-lml/x-lmlpack' => 'lmlpack',
        'application/x-lzh' => 'lzh',
        'audio/ma1' => 'ma1',
        'audio/ma2' => 'ma2',
        'audio/ma3' => 'ma3',
        'audio/ma5' => 'ma5',
        'magnus-internal/imagemap' => 'map',
        'application/mbedlet' => 'mbd',
        'application/x-mascot' => 'mct',
        'text/x-vmel' => 'mel',
        'application/x-mif' => 'mi mif',
        'audio/midi' => 'mid midi',
        'audio/x-mio' => 'mio',
        'application/x-skt-lbs' => 'mmf',
        'video/x-mng' => 'mng',
        'application/x-mocha' => 'moc mocha',
        'application/x-yumekara' => 'mof',
        'chemical/x-mdl-molfile' => 'mol',
        'chemical/x-mopac-input' => 'mop',
        'audio/x-mpeg' => 'mp2 mp3',
        'video/mp4' => 'mp4 mpg4',
        'application/vnd.mpohun.certificate' => 'mpc',
        'application/vnd.mophun.application' => 'mpn',
        'application/x-mapserver' => 'mps',
        'text/x-mrml' => 'mrl',
        'application/x-mrm' => 'mrm',
        'application/metastream' => 'mts mtx mtz mzv rtg',
        'image/nbmp' => 'nbmp',
        'x-lml/x-ndb' => 'ndb',
        'application/ndwn' => 'ndwn',
        'application/x-nif' => 'nif',
        'application/x-scream' => 'nmz',
        'image/vnd.nok-oplogo-color' => 'nokia-op-logo',
        'application/x-netfpx' => 'npx',
        'audio/nsnd' => 'nsnd',
        'application/x-neva1' => 'nva',
        'application/x-AtlasMate-Plugin' => 'oom',
        'audio/x-pac' => 'pac',
        'audio/x-epac' => 'pae',
        'application/x-pan' => 'pan',
        'image/x-pcx' => 'pcx',
        'image/x-pda' => 'pda',
        'chemical/x-pdb' => 'pdb xyz',
        'application/font-tdpfr' => 'pfr',
        'image/x-pict' => 'pict',
        'application/x-perl' => 'pm',
        'application/x-pmd' => 'pmd',
        'image/png' => 'png pnz',
        'application/x-cprplayer' => 'pqf',
        'application/cprplayer' => 'pqi',
        'application/x-prc' => 'prc',
        'application/x-ns-proxy-autoconfig' => 'proxy',
        'application/listenup' => 'ptlk',
        'video/x-pv-pvx' => 'pvx',
        'audio/vnd.qcelp' => 'qcp',
        'image/x-quicktime' => 'qti qtif',
        'text/vnd.rn-realtext3d' => 'r3t',
        'application/x-rar-compressed' => 'rar',
        'application/rdf+xml' => 'rdf',
        'image/vnd.rn-realflash' => 'rf',
        'application/x-richlink' => 'rlf',
        'audio/x-rmf' => 'rmf',
        'application/vnd.rn-realplayer' => 'rnx',
        'image/vnd.rn-realpix' => 'rp',
        'audio/x-pn-realaudio-plugin' => 'rpm',
        'text/vnd.rn-realtext' => 'rt',
        'x-lml/x-gps' => 'rte trk wpt',
        'video/vnd.rn-realvideo' => 'rv',
        'application/x-rogerwilco' => 'rwc',
        'application/x-supercard' => 'sca',
        'application/e-score' => 'sdf',
        'text/x-sgml' => 'sgm sgml',
        'magnus-internal/parsed-html' => 'shtml',
        'application/presentations' => 'shw',
        'image/si6' => 'si6',
        'image/vnd.stiwap.sis' => 'si7',
        'image/vnd.lgtwap.sis' => 'si9',
        'application/vnd.symbian.install' => 'sis',
        'application/x-Koan' => 'skd skm skp skt',
        'application/x-salsa' => 'slc',
        'audio/x-smd' => 'smd smz',
        'application/smil' => 'smi smil',
        'application/studiom' => 'smp',
        'text/x-speech' => 'spc talk',
        'application/x-sprite' => 'spr sprite',
        'application/x-spt' => 'spt',
        'application/hyperstudio' => 'stk',
        'image/vnd' => 'svf',
        'image/svg-xml' => 'svg',
        'image/svh' => 'svh',
        'x-world/x-svr' => 'svr',
        'application/x-shockwave-flash' => 'swf swfl',
        'application/x-timbuktu' => 'tbp tbt',
        'application/vnd.eri.thm' => 'thm',
        'application/x-tkined' => 'tki tkined',
        'application/toc' => 'toc',
        'image/toy' => 'toy',
        'audio/tsplayer' => 'tsi',
        'application/dsptype' => 'tsp',
        'application/t-time' => 'ttz',
        'application/x-uuencode' => 'uu uue',
        'application/x-cdlink' => 'vcd',
        'video/vdo' => 'vdo',
        'audio/vib' => 'vib',
        'video/vivo' => 'viv vivo',
        'application/vocaltec-media-desc' => 'vmd',
        'application/vocaltec-media-file' => 'vmf',
        'application/x-dreamcast-vms-info' => 'vmi',
        'application/x-dreamcast-vms' => 'vms',
        'audio/voxware' => 'vox',
        'audio/x-twinvq-plugin' => 'vqe',
        'audio/x-twinvq' => 'vqf vql',
        'x-world/x-vream' => 'vre vrw',
        'x-world/x-vrt' => 'vrt',
        'workbook/formulaone' => 'vts',
        'audio/x-ms-wax' => 'wax',
        'image/vnd.wap.wbmp' => 'wbmp',
        'application/vnd.xara' => 'web xar',
        'image/wavelet' => 'wi',
        'application/x-InstallShield' => 'wis',
        'video/x-ms-wm' => 'wm',
        'audio/x-ms-wma' => 'wma',
        'application/x-ms-wmd' => 'wmd',
        'text/vnd.wap.wml' => 'wml',
        'application/vnd.wap.wmlc' => 'wmlc',
        'text/vnd.wap.wmlscript' => 'wmls wmlscript ws',
        'application/vnd.wap.wmlscriptc' => 'wmlsc wsc',
        'audio/x-ms-wmv' => 'wmv',
        'video/x-ms-wmx' => 'wmx',
        'application/x-ms-wmz' => 'wmz',
        'image/x-up-wpng' => 'wpng',
        'video/wavelet' => 'wv',
        'video/x-ms-wvx' => 'wvx',
        'application/x-wxl' => 'wxl',
        'application/x-xdma' => 'xdm xdma',
        'application/vnd.fujixerox.docuworks' => 'xdw',
        'application/xhtml+xml' => 'xht xhtm xhtml',
        'application/x-excel' => 'xll',
        'text/xml' => 'xml xsit xsl',
        'application/x-xpinstall' => 'xpi',
        'text/xul' => 'xul',
        'application/x-yz1' => 'yz1',
        'application/x-zaurus-zac' => 'zac',
    );
    if (empty($mimes[$mime]))
        return '';
    return current(explode(' ',$mimes[$mime]));
}