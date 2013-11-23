<?php
/*
 * BOM CLEANER by Emrah Gunduz
 * 
 * Hi,
 * This is a single php file for cleaning the UTF8 byte mark order from files on
 * a hosted web site. The tool searches for every directory and included files
 * where it's located. So simply put it to the top folder of your site and run.
 * 
 * The script can be called from the web and is also capable of running from the
 * terminal screen, is also designed for both linux and windows based systems.
 * 
 * Script will alert you if it finds bom in any file, and if possible cleans it.
 * If a file cannot be read you will be notified.
 * 
 * If you want this to work fully, give the script both write and read permissions.
 * 
 * And before you run it, please get a backup of your whole site!
 * 
 * WARNING:
 * The program is distributed in the hope that it will be useful, but without 
 * any warranty. The entire risk as to the quality and performance of the 
 * program is with you. In no event the author will be liable to you for damages, 
 * including any general, special, incidental or consequential damages arising 
 * out of the use or inability to use the program (including but not limited to 
 * loss of data or data being rendered inaccurate or losses sustained by you or 
 * third parties or a failure of the program to operate with any other programs), 
 * even if the author has been advised of the possibility of such damages.
 *
 * THIS SOFTWARE IS AND CAN BE DISTRIBUTED UNDER MIT LICENSE
 * 
 */
// Define some PHP settings
@ini_set( 'zlib.output_compression', 0 );
@ini_set( 'implicit_flush', 1 );
@ob_end_clean();
// We do not want the script to stop working for long processes
set_time_limit( 0 );
ob_implicit_flush( 1 );

/**
 * Detect if we are runnning under Windows
 */
define( 'WIN', strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' );
/**
 * Current version
 */
define( 'VERSION', 0.55 );
/**
 * The folder script resides under...
 */
define( 'ROOT', dirname( __FILE__ ) );

// Terminal color definitions
define( 'COLOR_BLACK', WIN ? "" : "\033[0;30m" );
define( 'COLOR_RED', WIN ? "" : "\033[0;31m" );
define( 'COLOR_GREEN', WIN ? "" : "\033[0;32m" );
define( 'COLOR_YELLOW', WIN ? "" : "\033[0;33m" );
define( 'COLOR_BLUE', WIN ? "" : "\033[0;34m" );
define( 'COLOR_PURPLE', WIN ? "" : "\033[0;35m" );
define( 'COLOR_CYAN', WIN ? "" : "\033[0;36m" );
define( 'COLOR_WHITE', WIN ? "" : "\033[0;37m" );
define( 'COLOR_RESET', WIN ? "" : "\033[0m" );
define( 'COLOR_BLACK_BLINK', WIN ? "" : "\033[5;30m" );
define( 'COLOR_RED_BLINK', WIN ? "" : "\033[5;31m" );
define( 'COLOR_GREEN_BLINK', WIN ? "" : "\033[5;32m" );
define( 'COLOR_YELLOW_BLINK', WIN ? "" : "\033[5;33m" );
define( 'COLOR_BLUE_BLINK', WIN ? "" : "\033[5;34m" );
define( 'COLOR_PURPLE_BLINK', WIN ? "" : "\033[5;35m" );
define( 'COLOR_CYAN_BLINK', WIN ? "" : "\033[5;36m" );
define( 'COLOR_WHITE_BLINK', WIN ? "" : "\033[5;37m" );
define( 'COLOR_RESET_BLINK', WIN ? "" : "\033[5m" );

/*
 * Check if we are running in a terminal or called from web
 */
if ( PHP_SAPI == 'cli' ) {
  $types = array();
  echo COLOR_YELLOW . "\n" .
      " ____                    _\n" .
      "|  _ \                  | |\n" .
      "| |_) | ___  _ __ ___   | |\n" .
      "|  _ < / _ \| '_ ` _ \  | |\n" .
      "| |_) | (_) | | | | | | |_|\n" .
      "|____/ \___/|_| |_| |_| (_)\n" .
      "        version  " . VERSION . COLOR_RESET . "\n\n\n";

  echo COLOR_WHITE . "I will clean the BOM headers from the files. Let's start!\n";

  echo COLOR_RED . "Did you back up your files? (yes/no): ";
  $handle = fopen( "php://stdin", "r" );
  $line = fgets( $handle );
  if ( trim( $line ) != 'yes' ) {
    echo "Backup first, I will not take responsibility if something goes wrong.\n\n";
    echo COLOR_RESET;
    exit();
  }

  echo COLOR_WHITE;
  echo "Answer with a y/n for the file types...\n\n";
  echo "php         : ";
  keepTrackOfTypes( "php" );
  echo "html        : ";
  keepTrackOfTypes( "html" );
  echo "htm         : ";
  keepTrackOfTypes( "htm" );
  echo "css         : ";
  keepTrackOfTypes( "css" );
  echo "js          : ";
  keepTrackOfTypes( "js" );
  echo "txt         : ";
  keepTrackOfTypes( "txt" );

  echo "Would you like some more file types to be checked?\n";
  echo "Write them by seperating by a colon like: xml, json,...\n";
  echo "Go : ";
  keepTrackOfTypesString();

  if ( !count( $types ) ) {
    echo COLOR_RED_BLINK . "If you do not want to check any file, ";
    echo "why are you using this?" . COLOR_RESET . "\n\n";
    exit();
  }

  echo "\nStarting...\n\n";
  cleanFiles();

  echo COLOR_YELLOW;
  echo "\nIf you are finished with me, delete me.\n";
  echo "I am reachable from the web, remember...\n\n";
  echo COLOR_RESET;
  exit();
}

/**
 * Keeps the answers of file extension questions.
 * Called from terminal.
 * @global array $types
 * @param string $t
 */
function keepTrackOfTypes ( $t )
{
  global $types;
  echo COLOR_GREEN;
  $handle = fopen( "php://stdin", "r" );
  $line = fgets( $handle );
  if ( trim( $line ) == 'yes' || trim( $line ) == 'y' ) {
    array_push( $types, $t );
  }
  echo COLOR_WHITE;
}

/**
 * Keeps the answers of file extension questions.
 * Called from web.
 * @global array $types
 * @internal param string $t
 */
function keepTrackOfTypesString ()
{
  global $types;
  echo COLOR_GREEN;
  $handle = fopen( "php://stdin", "r" );
  $line = fgets( $handle );
  $fs = explode( ",", trim( $line ) );
  foreach ( $fs as $f ) {
    if ( strlen( $f ) ) {
      array_push( $types, trim( str_ireplace( ".", "", $f ) ) );
    }
  }
  echo COLOR_WHITE;
}

/**
 * Reads file and folders and performs cleaning, is an internal function.
 * Called from terminal.
 * @global array $types
 * @param mixed $HOME
 */
function cleanFiles ( $HOME = false )
{
  global $types;

  $slash = WIN ? "\\" : "/";
  $folder = dir( $HOME ? $HOME : ROOT );

  echo COLOR_CYAN . "            " . ( $HOME ? $HOME : ROOT ) . "\n";

  $foundfolders = array();
  $error = 0;
  while ( $file = $folder->read() ) {
    $thisfile = ( $HOME ? $HOME : ROOT ) . $slash . $file;
    if ( $file != "." && $file != ".." ) {
      if ( is_dir( $thisfile ) ) {
        array_push( $foundfolders, $thisfile );
      } else {
        if ( in_array( pathinfo( $thisfile, PATHINFO_EXTENSION ), $types ) ) {
          $content = file_get_contents( $thisfile );
          if ( hasBom( $content ) ) {
            $content = substr( $content, 3 );
            if ( !file_put_contents( $thisfile, $content ) ) {
              echo COLOR_RED_BLINK . "< ERROR ! > {$thisfile}";
              $error++;
            } else {
              echo COLOR_GREEN . "< CLEANED > {$thisfile}";
            }
          } else {
            echo COLOR_BLUE . "< OK >      {$thisfile}";
          }
          echo "\n";
        }
      }
    }
  }
  $folder->close();

  if ( count( $foundfolders ) > 0 ) {
    foreach ( $foundfolders as $folder ) {
      cleanFiles( $folder );
    }
  }

  if ( $error ) {
    echo COLOR_RED . "There were {$error} files I could not read.\n";
    echo "Check file permissions, make me readable for all files (0777 is good)\n";
  }
}

/**
 * Reads file and folders and performs cleaning, is an internal function.
 * Called from web.
 * @global array $types
 * @param mixed $HOME
 */
function cleanFilesHtml ( $HOME = false )
{
  global $types;

  $slash = WIN ? "\\" : "/";
  $folder = dir( $HOME ? $HOME : ROOT );

  echo '<span style="color:#C2D4FF; font-size:13px;">' . ( $HOME ? $HOME : ROOT ) . '</span><br/>';

  $foundfolders = array();
  $error = 0;
  while ( $file = $folder->read() ) {
    $thisfile = ( $HOME ? $HOME : ROOT ) . $slash . $file;
    if ( $file != "." && $file != ".." ) {
      if ( is_dir( $thisfile ) ) {
        array_push( $foundfolders, $thisfile );
      } else {
        if ( in_array( pathinfo( $thisfile, PATHINFO_EXTENSION ), $types ) ) {
          $content = file_get_contents( $thisfile );
          if ( hasBom( $content ) ) {
            $content = substr( $content, 3 );
            if ( !file_put_contents( $thisfile, $content ) ) {
              echo '<span style="color:#C95036; font-size:13px;">' . "< ERROR ! > {$thisfile}";
              $error++;
            } else {
              echo '<span style="color:#2AC55F; font-size:13px;">' . "< CLEANED > {$thisfile}";
            }
          } else {
            echo '<span style="color:#5C87AE; font-size:13px;">' . "< OK > {$thisfile}";
          }
          echo '</span><br/>';
        }
      }
    }
  }
  $folder->close();

  if ( count( $foundfolders ) > 0 ) {
    foreach ( $foundfolders as $folder ) {
      cleanFilesHtml( $folder );
    }
  }

  if ( $error ) {
    echo '<p style="color:#EE3A4B; font-size:13px; margin-bottom:80px; margin-top:80px;">';
    echo "There were {$error} files I could not read." . '<br/>';
    echo "Check file permissions, make me readable for all files (0777 is good)" . '</p>';
  }
}

/**
 * Checks if the string has BOM
 * @param string $string
 * @return bool
 */
function hasBom ( $string )
{
  return ( substr( $string, 0, 3 ) == pack( "CCC", 0xef, 0xbb, 0xbf ) );
}

// Running from web
if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'run' ) {
  $types = filter_var_array( $_POST[ 'ext' ], FILTER_SANITIZE_STRING );
  $others = filter_var( $_POST[ 'types' ], FILTER_SANITIZE_STRING );
  if ( strlen( $others ) ) {
    $fs = explode( ",", trim( $others ) );
    foreach ( $fs as $f ) {
      if ( strlen( $f ) ) {
        array_push( $types, trim( str_ireplace( ".", "", $f ) ) );
      }
    }
  }
  cleanFilesHtml();

  echo '<p style="color:#32AE5E; font-size:13px; margin-bottom:80px; margin-top:80px;">';
  echo 'If you are finished with me, delete me. ';
  echo 'I am reachable from the web, remember...</p>';

  exit();
}

/**
 * This is a copy of the Bootstrap v2.3.1 framework gzip compressed and base64
 * encoded for keeping everything in a single file. Copyright information can be
 * found in the html rendering of this file.
 */
define( 'BOOTSTRAPCSS', "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
/ZHvdtVPRny++z/9fw==" );

$comp = base64_decode( trim( BOOTSTRAPCSS ) );
$boot = gzinflate( $comp );
?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bom Cleaner v.<?php echo VERSION; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style><?php echo $boot; ?></style>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  </head>
  <body>

  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="brand" href="<?php echo basename( __FILE__ ); ?>">Bom Cleaner</a>
      </div>
    </div>
  </div>

  <div class="container" style="margin-top: 80px;">
    <div class="hero-unit">

      <form class="bs-docs-example form-inline">
        <h1>Select types</h1>

        <p class="marketing-byline">I'll be starting to check from <?php echo ROOT; ?></p>
        <br/>

        <p class="marketing-byline">Choose the extensions you would like to be checked for BOM</p>

        <div class="control-group">
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="php" name="type_php" checked="checked">
            php
          </label>
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="htm" name="type_htm" checked="checked">
            htm
          </label>
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="html" name="type_html" checked="checked">
            html
          </label>
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="css" name="type_css">
            css
          </label>
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="js" name="type_js">
            js
          </label>
          <label class="checkbox inline">
            <input type="checkbox" class="confirm" value="txt" name="type_txt">
            txt
          </label>
          <input type="text" class="input-xlarge othertypes" style="margin-left: 20px;" placeholder="Other types (ex: xml, json,...)">
          <button type="submit" class="btn" style="margin-left: 20px;">Start</button>
          <br/>
          <label class="checkbox inline">
            <input type="checkbox" id="sendform" value="1" name="backup">
            Did you back up your files? I will not take responsibility if something goes wrong.
          </label>

        </div>
      </form>

    </div>

    <div class="echotext" style="display:none;">
      <h1>Files and folders</h1>

      <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Please wait I'm working!</strong><br/>It might take a while before I finish checking. Do not close your this page or browser...
      </div>
    </div>
  </div>
  <script>
    $(document).ready(function () {
      $('form').on('submit', function (e) {
        e.preventDefault();

        if (!$('#sendform').is(':checked')) {
          alert("Backup first. I'll not continue until that checkbox is checked.");
          return false;
        }

        var compile = {
          'types': $('.othertypes').val(),
          'ext': []
        };

        $('.confirm').each(function () {
          compile.ext.push($(this).val());
        });

        $('.hero-unit').slideUp('fast');

        $('.echotext').slideDown('fast');

        // Jquery ajax command
        $.ajax({
          type: 'POST',
          data: compile,
          dataType: 'html', // xml, json, script or html
          async: true,
          contentType: 'application/x-www-form-urlencoded;charset=UTF8',
          url: "?action=run",
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("ERROR: " + textStatus + "\n" + errorThrown);
          },
          success: function (data) {
            $('.echotext').append(data);
            $('.alert').slideUp('fast');
          }
        });

        return false;
      });
    });
  </script>
  </body>
  </html>
<?php
