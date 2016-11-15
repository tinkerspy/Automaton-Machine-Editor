# Automaton-Machine-Editor
Web based editor - Generates templates for table based Automaton State Machines

https://github.com/tinkerspy/Automaton/wiki

Requirements:
- PHP 5+ (5.3 tested)
- PHP session support enabled
- Linux web host (other unix might work)
- Git (version 1.7.1 tested) 
- Writable 'machines' subdirectory
- Internet connection (for CDN based Javascript libraries)

Javascript libraries (linked via CDN)
- Jquery 1.12.2
- Twitter Bootstrap 3.3.6
- Highlight.js 9.4.0

Troubleshooting:
- If your http server can't write to the machines subdirectory try
disabling SELinux and rebooting.
- If you get a 'Call to undefined function simplexml_load_string()' error in Ubuntu
try installing php7.0.xml: sudo apt-get install php7.0-xml

