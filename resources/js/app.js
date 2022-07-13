require('./bootstrap');
require('./utils');

import Alpine from 'alpinejs';
import { on } from "./on";

window.Alpine = Alpine;
window.on = on;

Alpine.start();
