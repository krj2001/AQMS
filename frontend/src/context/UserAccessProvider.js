import {
  createContext, useContext, useState,
} from 'react';
import ApplicationStore from '../utils/localStorageUtil';
import { crudConfig } from '../config/roleConfig';

const UserAccessContext = createContext();
const LatestAlertContext = createContext();

export function useUserAccess() {
  return useContext(UserAccessContext);
}

export function LatestAlertAccess() {
  return useContext(LatestAlertContext);
}

export function UserAccessProvider({ children }) {
  // eslint-disable-next-line react/jsx-no-constructed-context-values
  const requestAccess = (moduleToAccess) => {
    const { userDetails } = ApplicationStore().getStorage('userDetails');
    // const newNotification = ApplicationStore().getStorage('notificationDetails');
    const moduleConfig = crudConfig[moduleToAccess.toLowerCase()];
    const userAccess = moduleConfig[userDetails.userRole.toLowerCase()];
    return userAccess;
  };

  return (
    <UserAccessContext.Provider value={requestAccess}>
      {children}
    </UserAccessContext.Provider>
  );
}

export function LatestAlertProvider({ children }) {
  const [alertStatus, setAlertStatus] = useState(false);

  return (
    // eslint-disable-next-line react/jsx-no-constructed-context-values
    <LatestAlertContext.Provider value={{ alertStatus, setAlertStatus }}>
      {children}
    </LatestAlertContext.Provider>
  );
}
