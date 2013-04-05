<?php
@ini_set( 'zlib.output_compression', 0 );
@ini_set( 'implicit_flush', 1 );
@ob_end_clean();
set_time_limit( 0 );
ob_implicit_flush( 1 );

define( 'VERSION', 0.5 );
define( 'ROOT', dirname( __FILE__ ) );
define( 'COLOR_BLACK', "\033[0;30m" );
define( 'COLOR_RED', "\033[0;31m" );
define( 'COLOR_GREEN', "\033[0;32m" );
define( 'COLOR_YELLOW', "\033[0;33m" );
define( 'COLOR_BLUE', "\033[0;34m" );
define( 'COLOR_PURPLE', "\033[0;35m" );
define( 'COLOR_CYAN', "\033[0;36m" );
define( 'COLOR_WHITE', "\033[0;37m" );
define( 'COLOR_RESET', "\033[0m" );
define( 'COLOR_BLACK_BLINK', "\033[5;30m" );
define( 'COLOR_RED_BLINK', "\033[5;31m" );
define( 'COLOR_GREEN_BLINK', "\033[5;32m" );
define( 'COLOR_YELLOW_BLINK', "\033[5;33m" );
define( 'COLOR_BLUE_BLINK', "\033[5;34m" );
define( 'COLOR_PURPLE_BLINK', "\033[5;35m" );
define( 'COLOR_CYAN_BLINK', "\033[5;36m" );
define( 'COLOR_WHITE_BLINK', "\033[5;37m" );
define( 'COLOR_RESET_BLINK', "\033[5m" );
define( 'WIN', strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' );

if ( PHP_SAPI == 'cli' ) {
  $types = array( );
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

function keepTrackOfTypes ( $t ) {
  global $types;
  echo COLOR_GREEN;
  $handle = fopen( "php://stdin", "r" );
  $line = fgets( $handle );
  if ( trim( $line ) == 'yes' || trim( $line ) == 'y' ) {
    array_push( $types, $t );
  }
  echo COLOR_WHITE;
}

function keepTrackOfTypesString () {
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

function cleanFiles ( $HOME = false ) {
  global $types;

  $slash = WIN ? "\\" : "/";
  $folder = dir( $HOME ? $HOME : ROOT  );

  echo COLOR_CYAN . "            " . ($HOME ? $HOME : ROOT) . "\n";

  $foundfolders = array( );
  $error = 0;
  while ( $file = $folder->read() ) {
    $thisfile = ($HOME ? $HOME : ROOT) . $slash . $file;
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

function cleanFilesHtml ( $HOME = false ) {
  global $types;

  $slash = WIN ? "\\" : "/";
  $folder = dir( $HOME ? $HOME : ROOT  );

  echo '<span style="color:#C2D4FF; font-size:13px;">' . ($HOME ? $HOME : ROOT) . '</span><br/>';

  $foundfolders = array( );
  $error = 0;
  while ( $file = $folder->read() ) {
    $thisfile = ($HOME ? $HOME : ROOT) . $slash . $file;
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

function hasBom ( $string ) {
  if ( substr( $string, 0, 3 ) == pack( "CCC", 0xef, 0xbb, 0xbf ) )
    return true;
  return false;
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
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  </head>
  <body>

    <!-- Navbar
    ================================================== -->
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
            <button type="submit" class="btn" style="margin-left: 20px;">Start</button><br/>
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
      $(document).ready(function(){
        $('form').on('submit', function(e){
          e.preventDefault();

          if(!$('#sendform').is(':checked')) {
            alert("Backup first. I'll not continue until that checkbox is checked.");
            return false;
          }

          var compile = {
            'types' : $('.othertypes').val(),
            'ext' : []
          };

          $('.confirm').each(function(){
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
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              console.log("ERROR: " + textStatus + "\n" + errorThrown);
            },
            success: function(data) {             
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
