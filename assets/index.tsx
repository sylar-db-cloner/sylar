import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { svgFavicon } from '@space-kit/hat';
import { SnackbarProvider } from 'notistack';
import App from './App';

import RawSvg from './components/LogoPicture';

svgFavicon(RawSvg);

const container = document.getElementById('root');
const root = createRoot(container!);
root.render(
  <BrowserRouter>
    <SnackbarProvider maxSnack={5}>
      <App />
    </SnackbarProvider>
  </BrowserRouter>,
);
