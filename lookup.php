<?php
// File mapping for od/ directory games
$games = [
    1 => '4thandinches.prg',
    2 => 'beach-head_ii.d64',
    3 => 'beachhead.prg',
    4 => 'defender64.prg',
    5 => 'DIGDUG.PRG',
    6 => 'dkongjunior.prg',
    7 => 'dkongocean.prg',
    8 => 'Galaga.prg',
    9 => 'Ghostbusters.prg',
    10 => 'tapper.prg',
    11 => 'jumpmanjunior.prg',
    12 => 'DIGDUG.PRG',
    13 => 'Leaderboard.prg',
    14 => 'musicprocessor.d64',
    15 => 'Pac-Land.prg',
    16 => '10thframe.prg',
    17 => 'Panzer.prg'
];

// Commodore 64 BASIC tokens
define('TOKEN_REM', "\x8F");
define('TOKEN_PRINT', "\x99");
define('TOKEN_INPUT', "\x85");
define('TOKEN_IF', "\x8B");
define('TOKEN_THEN', "\xA7");
define('TOKEN_OR', "\xB0"); // Set to \xB0 per user request
define('TOKEN_GOTO', "\x89");
define('TOKEN_ON', "\x91");
define('TOKEN_LOAD', "\x93");
define('TOKEN_END', "\x83");
define('TOKEN_RUN', "\x9A");
define('TOKEN_END_LINE', "\x00");

// Check if this is a request for the game listing
if (isset($_GET['id']) && $_GET['id'] == '99') {
    // Initialize .prg content with load address ($0801)
    $prgContent = pack('v', 0x0801); // Load address: 01 08
    $currentAddress = 0x0801 + 2; // Start after load address

    // Helper function to add a BASIC line
    function addBasicLine(&$prgContent, &$currentAddress, $lineNumber, $lineContent) {
        $nextAddress = $currentAddress + strlen($lineContent) + 5; // 2 bytes next addr, 2 bytes line num, content, null
        $prgContent .= pack('v', $nextAddress); // Next line address
        $prgContent .= pack('v', $lineNumber); // Line number
        $prgContent .= $lineContent; // Tokenized content
        $prgContent .= TOKEN_END_LINE; // Null terminator
        $currentAddress = $nextAddress;
    }

    // Line 10: REM ON DECK GAMES
    addBasicLine($prgContent, $currentAddress, 10, TOKEN_REM . " ON DECK GAMES");

    // Line 20: REM CLEAR SCREEN
    addBasicLine($prgContent, $currentAddress, 20, TOKEN_REM . " CLEAR SCREEN");

    // Line 30: PRINT "ON DECK GAMES"
    addBasicLine($prgContent, $currentAddress, 30, TOKEN_PRINT . "\"ON DECK GAMES\"");

    // Line 40: PRINT
    addBasicLine($prgContent, $currentAddress, 40, TOKEN_PRINT);

    // Game list
    $lineNumber = 50;
    foreach ($games as $id => $filename) {
        $gameName = strtoupper(pathinfo($filename, PATHINFO_FILENAME));
        $lineContent = TOKEN_PRINT . "\"" . sprintf("%2d. %-20s", $id, $gameName) . "\"";
        addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
        $lineNumber += 10;
    }

    // Line 220: PRINT
    addBasicLine($prgContent, $currentAddress, 220, TOKEN_PRINT);

    // Line 230: PRINT "ENTER GAME NUMBER (1-17):"
    addBasicLine($prgContent, $currentAddress, 230, TOKEN_PRINT . "\"ENTER GAME NUMBER (1-17):\";");

    // Line 240: INPUT N
    addBasicLine($prgContent, $currentAddress, 240, TOKEN_INPUT . "N");

    // Line 250: IF N<1 OR N>17 THEN PRINT "INVALID NUMBER!" : GOTO 230
    $lineNumber = 250;
    $promptLine = 230; // Points to PRINT "ENTER GAME NUMBER (1-17):"
    $lineContent = TOKEN_IF . " N\x3C1 " . TOKEN_OR . " N\x3E17 " . TOKEN_THEN . " " . TOKEN_PRINT . "\"INVALID NUMBER!\"" . "\x3A" . TOKEN_GOTO . strval($promptLine);
    error_log("Line 250 content (hex): " . bin2hex($lineContent)); // Debug token content
    addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
    $lineNumber += 10;

    // Line 260: ON N GOTO 270,300,...,750
    $maxGames = count($games);
    $gotoLines = range(270, 270 + ($maxGames - 1) * 30, 30); // 270, 300, ..., 750
    $lineContent = TOKEN_ON . "N" . TOKEN_GOTO . implode(",", $gotoLines);
    addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
    $lineNumber += 10;

    // PRINT, LOAD, and RUN commands
    foreach ($games as $id => $filename) {
        $gameName = strtoupper(pathinfo($filename, PATHINFO_FILENAME));
        $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
        // PRINT "LOADING <GAME NAME>"
        $lineContent = TOKEN_PRINT . "\"LOADING " . $gameName . "\"";
        addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
        $lineNumber += 10;
        // LOAD "HTTP://RDEANM.COM/OD/<ID>.<EXT>",8,1
        $lineContent = TOKEN_LOAD . "\"HTTP://RDEANM.COM/OD/" . $id . "." . $extension . "\",8,1";
        addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
        $lineNumber += 10;
        // RUN
        $lineContent = TOKEN_RUN;
        addBasicLine($prgContent, $currentAddress, $lineNumber, $lineContent);
        $lineNumber += 10;
    }

    // Line 780: END
    addBasicLine($prgContent, $currentAddress, $lineNumber, TOKEN_END);

    // End of program
    $prgContent .= "\x00\x00";

    // Set headers for .prg file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="listing.prg"');
    header('Content-Length: ' . strlen($prgContent));

    // Output the .prg file
    echo $prgContent;
    exit;
} elseif (isset($_GET['id']) && $_GET['id'] == '0') {
    // Pick a random game
    $randomId = array_rand($games);
    $filename = $games[$randomId];
} else {
    // Handle numbered requests (1.prg, 2.prg, etc.)
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!isset($games[$id])) {
        http_response_code(404);
        echo "Game #$id not found";
        exit;
    }
    
    $filename = $games[$id];
}

// Full path to the actual file
$filepath = __DIR__ . '/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    echo "File not found: $filename";
    exit;
}

// Set appropriate headers for C64 files
header('Content-Type: application/octet-stream');
header('Content-Disposition: inline; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filepath));

// Serve the file
readfile($filepath);
?>
