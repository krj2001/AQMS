import { useEffect, useState } from 'react';
import { Outlet, useNavigate } from 'react-router-dom';
import Sidebar from '../components/navbarComponent/Sidebar';
import Navbar from '../components/navbarComponent/Navbar';
import './css/home.scss';
import { LatestAlertProvider, UserAccessProvider } from '../context/UserAccessProvider';
import ApplicationStore from '../utils/localStorageUtil';
import {
  BuildingFetchService,
  DeviceFetchService,
  FetchBranchService, FetchFacilitiyService, FetchLocationService, FloorfetchService, LabfetchService, NotificationAlerts,
} from '../services/LoginPageService';
import GlobalNotifier from '../components/notification/GlobalNotificationBar';
import { acknowledgedAlertvalidator, alertSeverityCode, setAlertColor } from '../utils/helperFunctions';
import { Backdrop, CircularProgress, Fab, Grid } from '@mui/material';
import VolumeOffIcon from '@mui/icons-material/VolumeOff';
import Box from '@mui/material/Box';

import CampaignIcon from '@mui/icons-material/Campaign';
/* eslint-disable no-unused-vars */
/* eslint-disable no-shadow */
/* eslint-disable no-nested-ternary */
/* eslint-disable array-callback-return */
/* eslint-disable radix */

function HomePageComponent() {
  const navigate = useNavigate();
  const [locationLabel, setLocationLabel] = useState('');
  const [branchLabel, setBranchLabel] = useState('');
  const [facilityLabel, setFacilityLabel] = useState('');
  const [buildingLabel, setBuildingLabel] = useState('');
  const [floorLabel, setFloorLabel] = useState('');
  const [labLabel, setLabLabel] = useState('');
  const [mobileMenu, setMobileOpen] = useState(true);
  const [backdropOpen, setBackdropOpen] = useState(false);
  const [notifierState, setNotifierState] = useState({
    open: false,
    message: 'You have new notification !',
    color: '#ffca28', // amber : '#ffca28', green: '#4caf50'
  });
  const [newNotification, setNewNotification] = useState(false);
  const [anchorElNotification, setAnchorElNotification] = useState(null);
  const { notificationList } = ApplicationStore().getStorage('notificationDetails');
  const { locationDetails, userDetails, intervalDetails } = ApplicationStore().getStorage('userDetails');
  const { navigateDashboard } = ApplicationStore().getStorage('navigateDashboard');
  const [newAlertList, setnewAlertList] = useState([]);
  const {
    location_id, branch_id, facility_id, building_id, floor_id, lab_id
  } = locationDetails;

  const { locationIdList, branchIdList, facilityIdList, buildingIdList, floorIdList,
    labIdList, deviceIdList, sensorIdList, } = ApplicationStore().getStorage('alertDetails');
  const intervalSec = intervalDetails.alertLogInterval * 1000 || 10000;
  const [notificationCount, setNotificationCount] = useState(notificationList?.length);
  const [latestAlerTId,setLatestAlerTId] = useState('');
  const [lastAcknowledgedAlert,setLastAcknowledgedAlert] = useState(userDetails?.lastAcknowledgedAlert);
  const [notificationId,setnotificationId] = useState(userDetails?.notificationId);

  const [mute,setMute]=useState(true);

  useEffect(() => {
    
    // console.log('Interval running...');
    if (userDetails.userRole !== 'superAdmin') {
      ApplicationStore().setStorage('siteDetails', {
        locationLabel, branchLabel, facilityLabel, buildingLabel, floorLabel, labLabel
      });
      const notifierInterval = setInterval(() => {
        NotificationAlerts({ location_id, branch_id, facility_id, building_id, floor_id, lab_id }, handleNotificationSuccess, handleNotificationException);
      }, intervalSec); // set to 'intervalSec' after testing alert call

      return () => {
        clearInterval(notifierInterval);
      };
    }
  });

  useEffect(() => {
    const { userDetails } = ApplicationStore().getStorage('userDetails');
    if (userDetails?.forcePasswordReset === 1) {
      return navigate('/passwordReset');
    }
    if (userDetails?.secondLevelAuthorization === 'true') {
      return navigate('/otp');
    }
    if (userDetails.userRole !== 'superAdmin') {
      if (lab_id) {
        fetchLab();
      } else if (floor_id) {
        fetchFloor();
      } else if (building_id) {
        fetchBuilding();
      } else if (facility_id) {
        fetchFacility();
      } else if (branch_id) {
        fetchBranch();
      } else if (location_id) {
        fetchLocation();
      } else {
        FetchLocationService(handleSuccess, handleException);
      }

      setBackdropOpen(true);
    }
    setTimeout(() => {
      if (userDetails.userRole !== 'superAdmin') {
        if (navigateDashboard === true) {
          setBackdropOpen(false);
          ApplicationStore().setStorage('navigateDashboard', { navigateDashboard: false });
          navigate('/Dashboard');
        }
      };
      setBackdropOpen(false);
    }, 3000);
  }, []);


  const fetchLocation = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
  }
  const fetchBranch = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
    FetchFacilitiyService({ location_id, branch_id }, handleFacilitySuccess, handleException);
  }

  const fetchFacility = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
    FetchFacilitiyService({ location_id, branch_id }, handleFacilitySuccess, handleException);
    BuildingFetchService({ location_id, branch_id, facility_id }, handleBuildingSuccess, handleException);
  }

  const fetchBuilding = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
    FetchFacilitiyService({ location_id, branch_id }, handleFacilitySuccess, handleException);
    BuildingFetchService({ location_id, branch_id, facility_id }, handleBuildingSuccess, handleException);
    FloorfetchService({ location_id, branch_id, facility_id, building_id }, handleFloorSuccess, handleException);
  }

  const fetchFloor = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
    FetchFacilitiyService({ location_id, branch_id }, handleFacilitySuccess, handleException);
    BuildingFetchService({ location_id, branch_id, facility_id }, handleBuildingSuccess, handleException);
    FloorfetchService({ location_id, branch_id, facility_id, building_id }, handleFloorSuccess, handleException);
    LabfetchService({ location_id, branch_id, facility_id, building_id, floor_id }, handleLabSuccess, handleException);
  }

  const fetchLab = () => {
    FetchLocationService(handleSuccess, handleException);
    FetchBranchService({ location_id }, handleBranchSuccess, handleException);
    FetchFacilitiyService({ location_id, branch_id }, handleFacilitySuccess, handleException);
    BuildingFetchService({ location_id, branch_id, facility_id }, handleBuildingSuccess, handleException);
    FloorfetchService({ location_id, branch_id, facility_id, building_id }, handleFloorSuccess, handleException);
    LabfetchService({ location_id, branch_id, facility_id, building_id, floor_id }, handleLabSuccess, handleException);
    // DeviceFetchService({location_id, branch_id, facility_id, building_id, floor_id, lab_id}, handleDeviceSuccess, handleException)
  }

  const handleSuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(location_id)) {
        setLocationLabel(datas.stateName);
      }
    });
  };
  const handleBranchSuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(branch_id)) {
        setBranchLabel(datas.branchName);
      }
    });
  };
  const handleFacilitySuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(facility_id)) {
        setFacilityLabel(datas.facilityName);
      }
    });
  };

  const handleBuildingSuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(building_id)) {
        setBuildingLabel(datas.buildingName);
      }
    });
  };

  const handleFloorSuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(floor_id)) {
        setFloorLabel(datas.floorName);
      }
    });
  };

  const handleLabSuccess = (dataObject) => {
    dataObject?.data.map((datas) => {
      if (datas.id === parseInt(lab_id)) {
        setLabLabel(datas.labDepName);
      }
    });
  };

  // const handleDeviceSuccess = (dataObject) => {
  //   dataObject?.data.map((datas) => {
  //     if (datas.id === parseInt(lab_id)) {
  //       setLabLabel(datas.facilityName);
  //     }
  //   });
  // };

  const handleException = () => { };

  const handleNotificationSuccess = (dataObject) => {
    // limit the notification count
    let newDataObject = dataObject.data.sort((firstElement, secondElement) => secondElement.id - firstElement.id).slice(0, 12);

    // Check for new alert with existing stack
    const arraySet = newDataObject.filter((object1) => {
      return !notificationList.some((object2) => {
        return object1.id === object2.id // || (object1.sensorId === object2.sensorId && object1.id <= object2.id );
      });
    });
    // make an alert if we have new alert
    console.log("lastAcknowledgedAlert",lastAcknowledgedAlert);
    console.log("lastDate",notificationId);
    let newNotificationValue = newNotification;
    if (arraySet.length !== 0) {
      // Declare static acknowledged alert
      // lastAcknowledgedAlert : "1551 24-01-2023 14:35:01"
      setLatestAlerTId(arraySet[0].id);
      let staticLastAcknowledgedAlert = {
        // dateTime: lastAcknowledgedAlert
        id: notificationId,
        dateTime: lastAcknowledgedAlert
      };
      
  
      
      //Add new alerts into list 
      setnewAlertList(arraySet);
      
      setNewNotification((oldValue) => {
        newNotificationValue = !oldValue;
        return !oldValue;
      });
      var colorObject = setAlertColor(arraySet);
      let showNotificationFlag = acknowledgedAlertvalidator(arraySet,staticLastAcknowledgedAlert);
      setNotifierState((oldValue) => {
        return {
          ...oldValue,
          open: showNotificationFlag, // true
          color: colorObject.color,
          message: colorObject.message,
        };
      });
      ApplicationStore().setStorage('notificationDetails', { notificationList: newDataObject, newNotification: newNotificationValue });
      setNotificationCount(newDataObject?.length);
    }
    
    ApplicationStore().setStorage('notificationDetails', {
      notificationList: newDataObject,
      newNotification: newNotificationValue
    });
    setNotificationCount(newDataObject?.length);

    let updatedAlertDetails = {
      locationIdList: [],
      branchIdList: [],
      facilityIdList: [],
      buildingIdList: [],
      floorIdList: [],
      labIdList: [],
      deviceIdList: [],
      sensorIdList: [],
    };

    newDataObject?.map((data, index) => {
      updatedAlertDetails = {
        locationIdList: [...updatedAlertDetails.locationIdList, {
          id: data.location_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        branchIdList: [...updatedAlertDetails.branchIdList, {
          id: data.branch_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        facilityIdList: [...updatedAlertDetails.facilityIdList, {
          id: data.facility_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        buildingIdList: [...updatedAlertDetails.buildingIdList, {
          id: data.building_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        floorIdList: [...updatedAlertDetails.floorIdList, {
          id: data.floor_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        labIdList: [...updatedAlertDetails.labIdList, {
          id: data.lab_id,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        deviceIdList: [...updatedAlertDetails.deviceIdList, {
          id: data.deviceId,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
        sensorIdList: [...updatedAlertDetails.sensorIdList, {
          id: data.sensorId,
          alertType: data.alertType,
          alertPriority: alertSeverityCode(data.alertType)
        }],
      };
    });

    ApplicationStore().setStorage('alertDetails', { ...updatedAlertDetails });
  };

  const handleNotificationException = () => { };

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileMenu);
  };

  const onAlertOff=()=>{
    setMute(oldValue => ! oldValue);
  }

  return (
    <div className="home bg-[#f3f3f3]">
      <Sidebar handleDrawerToggle={handleDrawerToggle} mobileMenu={mobileMenu} />
      <div className="homeContainer">
        <LatestAlertProvider >
          <GlobalNotifier
            notifierState={notifierState}
            setNotifierState={setNotifierState}
            anchorElNotification={anchorElNotification}
            setAnchorElNotification={setAnchorElNotification}
            newAlertList={newAlertList}
            latestAlerTId={latestAlerTId}
          />
          <Navbar
            handleDrawerToggle={handleDrawerToggle}
            mobileMenu={mobileMenu}
            notificationList={notificationList}
            anchorElNotification={anchorElNotification}
            setAnchorElNotification={setAnchorElNotification}
            notificationCount={notificationCount}
          />
         
         
          <div className={'h-[91vh] w-full overflow-auto lg:overflow-hidden'}>
            <UserAccessProvider>
              <Outlet />
              
            </UserAccessProvider>
           
          </div>
            {/* <Grid style={{display: 'flex',flexDirection:' row-reverse'}}>
              <Box sx={{ '& > :not(style)': { m: 1 } }} onClick={onAlertOff}>
                <Fab color="primary" aria-label="add">
                  {
                    !mute ?
                    <VolumeOffIcon /> : <CampaignIcon />
                  }
                </Fab>
              </Box>
          </Grid> */}
        </LatestAlertProvider>
      </div>
      <Backdrop
        sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
        open={backdropOpen}
      >
        <CircularProgress color="inherit" />
      </Backdrop>
    </div>
  );
}

export default HomePageComponent;
