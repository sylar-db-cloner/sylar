import { createContext, useContext } from 'react';

// @ts-ignore
const DarkModeContext = createContext();

export const useDarkMode = () => useContext(DarkModeContext);

export default DarkModeContext;
