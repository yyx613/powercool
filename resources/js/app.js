import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { StatusCodes } from 'http-status-codes'
window.StatusCodes = StatusCodes;

// Quill Editor
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Image Resize Module
import QuillResizeImage from 'quill-resize-image';
Quill.register('modules/resize', QuillResizeImage);

window.Quill = Quill;
