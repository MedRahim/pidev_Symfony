import { startStimulusApp } from '@symfony/stimulus-bundle';

// assets/bootstrap.js

// 1. Vos styles globaux
import './styles/app.css';

// 2. Bootstrap CSS + JS bundle
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import './controllers/explorer_controller.js';
import './controllers/garden_controller.js';
import './controllers/countup_controller.js';
import './controllers/flash_controller.js';

// 3. Démarrage de Stimulus via le bridge Symfony UX
import { startStimulusApp } from '@symfony/stimulus-bridge';

const app = startStimulusApp(
  require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
  )
);

export { app };