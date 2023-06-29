/* eslint-disable max-len */
import React from 'react';
import {
  HashRouter as Router, Routes, Route, Outlet, Navigate, useNavigate,
} from 'react-router-dom';
import './App.css';
import { LoadScript } from '@react-google-maps/api';
import LoginPage from './pages/LoginPageComponent';
import OneTimePassword from './pages/OneTimePasswordComponent';
// eslint-disable-next-line import/no-named-as-default
import HomePage from './pages/HomePageComponent';
import VendorManagement from './pages/VendorComponent';
import ForcePasswordReset from './pages/ForcePasswordResetComponent';
import CategoryManagement from './pages/CategoryManagementComponent';
import CustomerManagement from './pages/CustomerComponent';
import UserManagement from './pages/UserComponent';
import SiteDetails from './pages/SiteDetailsComponent';
import Dashboard from './components/DashboardComponent';
import Branch from './components/BranchComponent';
import Facility from './components/FacilityComponent';
import Building from './components/BuildingComponent';
import Floor from './components/FloorComponent';
import Lab from './components/LabComponent';
import UserResetPassword from './components/UserResetPassword';
import AddDeviceSensor from './components/AddDeviceSensorComponent';
import ApplicationStore from './utils/localStorageUtil';
import ManagementReportTab from './components/reportSectionComponents/ManagementReportTab';
import AppVersion from './components/AppVersion';
import GasCylinder from './pages/GasCylinderComponent';
import EmailConfig from './pages/EmailConfig';
import DeviceGridComponent from './components/dashboard/subComponent/siteDetailsComponent/DeviceGridComponent';

function ProtectedRoutes() {
  const navigate = useNavigate();
  const { user_token, userDetails } = ApplicationStore().getStorage('userDetails');
  if (user_token) {
    // if (userDetails?.secondLevelAuthorization === 'true' || userDetails?.forcePasswordReset === 1) {
    //   navigate('/login');
    // }
    if (userDetails?.secondLevelAuthorization === 'true') {
      navigate('/otp');
    }
    return <Outlet />;
  }

  return <Navigate replace to="/login" />;
}

function App() {
  return (
    <div className="App">
      <LoadScript googleMapsApiKey="AIzaSyBBv6shA-pBM0e9KydvwubSY55chq0gqS8">
        <Router>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route element={<ProtectedRoutes />}>
              <Route path="/otp" element={<OneTimePassword />} />
              <Route path="/passwordReset" element={<ForcePasswordReset />} />
              <Route path="/" element={<HomePage />}>
                <Route path="CustomerManagement/*" element={<CustomerManagement />} />
                <Route path="UserManagement/*" element={<UserManagement />} />
                <Route path="Vendor/*" element={<VendorManagement />} />
                <Route path="GasCylinder" element={<GasCylinder />} />
                <Route path="EmailConfig" element={<EmailConfig />} />
                <Route path="Report/*" element={<ManagementReportTab />} />
                <Route path="ChangePassword/*" element={<UserResetPassword />} />
                <Route path="AppVersion/*" element={<AppVersion />} />
                <Route path="Dashboard/*" element={<Dashboard />} />
                <Route path="Location/*" element={<SiteDetails />} />
                <Route path="Location/:locationId" element={<Branch />} />
                <Route path="Location/:locationId/:locationId" element={<Facility />} />
                <Route path="Location/:locationId/:locationId/:locationId/*" element={<Building />} />
                <Route path="Location/:locationId/:locationId/:locationId/:locationId/*" element={<Floor />} />
                <Route path="Location/:locationId/:locationId/:locationId/:locationId/:locationId/*" element={<Lab />} />
                <Route path="Location/:locationId/:locationId/:locationId/:locationId/:locationId/:locationId/*" element={<AddDeviceSensor />} />
                <Route path="Device/*" element={<CategoryManagement />} />
                <Route path="DeviceGridComponent/*" element={<DeviceGridComponent />} />
              </Route>
            </Route>
          </Routes>
        </Router>
      </LoadScript>
    </div>
  );
}

export default App;
