import './bootstrap';
import { parseLinkedInFile, sendToBackend } from './linkedin-parser.js';

// Rendre disponible globalement
window.parseLinkedInFile = parseLinkedInFile;
window.sendToBackend = sendToBackend;
