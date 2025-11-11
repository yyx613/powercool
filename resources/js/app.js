import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { StatusCodes } from 'http-status-codes'
window.StatusCodes = StatusCodes;

// Quill Editor
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
window.Quill = Quill;
