# Props - 
This is "my" Apache server side PHP softfware as I tinker with the Meatloaf by https://github.com/idolpx  https://github.com/idolpx/meatloaf I am just writiting add on software for me....  

# Meatloaf-Device-Loader-od-Directory
This repository powers the Meatloaf device—a multi-device emulator for the Commodore 64/128—by serving dynamically generated .prg files over HTTP. It uses PHP to construct tokenized BASIC programs compatible with C64 memory layout and Meatloaf's loading conventions.

#  Meatloaf Game Loader for Commodore 64

This project provides a dynamic PHP-based loader for the **Meatloaf multi-device emulator** by idolpx. It serves `.prg` and `.d64` files over HTTP, allowing Commodore 64 users to load games directly via Meatloaf's network interface.

## 📡 How It Works

The loader responds to specific HTTP requests from the C64 using Meatloaf's `LOAD` syntax:

### 🔹 Load Game Listing
```basic
LOAD"ML:OD"
```

This shortcode triggers a request to:
```
http://rdreanm.com/99.prg
```
Returns a tokenized BASIC program listing all available games with a menu interface.

### 🔹 Load Random Game
```basic
LOAD"HTTP://RDREANM.COM/0.PRG"
```
Returns a randomly selected game from the od/ directory.

### 🔹 Load Specific Game
```basic
LOAD"HTTP://RDREANM.COM/1.PRG"
LOAD"HTTP://RDREANM.COM/2.PRG"
...
LOAD"HTTP://RDREANM.COM/17.PRG"
```
Loads a specific game by index, mapped via the `$games[]` array in `loader.php`.

## 🎮 Available Games

The system currently hosts 17 classic Commodore 64 games including:
- 4th & Inches
- Beach Head II
- Defender 64
- Dig Dug
- Donkey Kong Jr.
- Galaga
- Ghostbusters
- Jumpman Jr.
- Leaderboard
- Pac-Land
- Panzer
- Tapper
- And more!

## 🧠 Technical Details

- All `.prg` files begin with the standard C64 load address `$0801`
- BASIC lines are tokenized using official Commodore 64 BASIC tokens
- The listing program (99.prg) is dynamically generated with proper memory addressing
- PHP handles line linking, token injection, and C64-compatible formatting
- Supports both `.prg` and `.d64` file formats

### Menu Program Features
- Displays numbered game list using tokenized BASIC
- Input validation for game selection (1-17)
- Dynamic `ON N GOTO` dispatching to load commands
- Proper error handling for invalid selections
- HTTP-based `LOAD` commands compatible with Meatloaf

## ⚙️ Server Configuration

### Apache .htaccess
```apache
# Handle od/ directory aliases (add this before other rules)
RewriteRule ^od/([0-9]+)\.prg$ od/loader.php?id=$1 [L]
```

This ensures that requests like `od/1.prg`, `od/99.prg`, or `od/0.prg` are dynamically handled by the PHP loader.

## 📁 Repository Structure
```
/od-loader/
├── src/
│   └── loader.php         # Main PHP logic for generating .prg files
├── games/
│   └── *.prg / *.d64      # Game files referenced by ID
├── docs/
│   └── meatloaf-spec.md   # Documentation on Meatloaf behavior
├── .htaccess              # Apache rewrite rules
├── .gitignore
└── README.md              # This file
```

## 🚀 Usage Examples

1. **Browse games**: Type `LOAD"ML:OD"` on your C64 to see the game menu
2. **Random play**: Use `LOAD"HTTP://RDREANM.COM/0.PRG"` for surprise selection
3. **Direct access**: Load specific games with `LOAD"HTTP://RDREANM.COM/5.PRG"` (loads Dig Dug)

## 🔧 PHP Implementation

The loader uses:
- Proper C64 BASIC token definitions (`TOKEN_REM`, `TOKEN_PRINT`, etc.)
- Dynamic `.prg` file generation with correct memory layout
- Game file mapping and HTTP response headers
- Error handling for missing files and invalid IDs

## 🙌 Credits

```php
// Meatloaf - A Commodore 64/128 multi-device emulator
// https://github.com/idolpx/meatloaf
// Copyright (C) 2022 James Johnston
```

Special thanks to (https://github.com/idolpx/meatloaf) for creating the amazing Meatloaf device that makes this modern C64 networking possible!

## 📝 License

This project is designed to work with the Meatloaf emulator. Please refer to the original Meatloaf repository for licensing information.
