import './bootstrap';
import { parseLinkedInFile, sendToBackend } from './linkedin-parser';

// Rendre disponible globalement
window.parseLinkedInFile = parseLinkedInFile;
window.sendToBackend = sendToBackend;
