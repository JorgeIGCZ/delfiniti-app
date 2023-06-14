require('./bootstrap');
require('./utils');
require('./tabs');

import Alpine from 'alpinejs';
import { on } from "./on";

window.Alpine = Alpine;
window.on = on;

Alpine.start();
