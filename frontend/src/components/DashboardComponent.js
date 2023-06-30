import React, { useEffect, useState } from 'react';
import './dashboard/dragResize.scss';
import { Grid } from '@mui/material';

import LocationGridWidget from './dashboard/components/LocationGridWidget';
import AlertWidget from './dashboard/components/AlertWidget';
import GeoLocationWidget from './dashboard/components/GeoLocationWidget';
import ImageMarkerList from './Device/subComponent/imageMarkerList';
import LandingPageComponent from './dashboard/subComponent/siteDetailsComponent/LandingPageComponent';
import DeviceGridComponent from './dashboard/subComponent/siteDetailsComponent/DeviceGridComponent';
import ApplicationStore from '../utils/localStorageUtil';
import { FetchFacilitiyService, FetchBranchService, FetchLocationService, DeviceIdAlerts, BuildingFetchService, FloorfetchService, LabfetchService } from '../services/LoginPageService';
import NotificationBar from './notification/ServiceNotificationBar';

const { locationLabel, facilityLabel, branchLabel, buildingLabel, floorLabel, labLabel } = ApplicationStore().getStorage('siteDetails');
/* eslint-disable no-unused-vars */
/* eslint-disable no-shadow */
function Dashboard() {
  const [locationDetails, setLocationDetails] = useState({
    location_id: '',
    branch_id: '',
    facility_id: '',
    building_id: '',
    floor_id: '',
    lab_id: '',
    device_id: '',
  });

  const [breadCrumbLabels, setBreadCrumbLabels] = useState({
    stateLabel: locationLabel || 'State',
    branchLabel: branchLabel || 'Branch',
    facilityLabel: facilityLabel || 'Facility',
    buildingLabel: buildingLabel || 'Building',
    floorLabel: floorLabel || 'Floor',
    labLabel: labLabel || 'Zone',
    deviceLabel: '',
  });

  const [siteImages, setSiteImages] = useState({
    buildingImage: '',
    floorImage: '',
    labImage: '',
  });

  const [zoomLevel, setZoomLevel] = useState(4);
  const [centerLatitude, setCenterLatitude] = useState(23.500);
  const [centerLongitude, setCenterLongitude] = useState(80.500);

  const [locationCoordinationList, setLocationCoordinationList] = useState([]);
  const [locationState, setProgressState] = useState(0);
  const [Img, setImg] = useState('');
  // const imgSrc = `http://localhost/backend/blog/public/${Img}`;
  const imgSrc = `${process.env.REACT_APP_API_ENDPOINT}blog/public/${Img}`;
  const [ImageState, setImageState] = useState(0);
  const [deviceCoordsList, setDeviceCoordsList] = useState([]);
  const [isdashboard, setIsDashBoard] = useState(0);
  const [isGeoMap, setIsGeoMap] = useState(true);
  const [alertList, setAlertList] = useState([]);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    const { locationLabel, facilityLabel, branchLabel, buildingLabel, floorLabel, labLabel } = ApplicationStore().getStorage('siteDetails');
    const { dashboardRefresh, labelCount } = ApplicationStore().getStorage('dashboardRefresh');

    setBreadCrumbLabels((oldValue) => {
      return {
        ...oldValue, stateLabel: locationLabel, branchLabel, facilityLabel, buildingLabel, floorLabel, labLabel
      };
    });
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    if(locationDetails?.imageBuildingURL){
      setImg(locationDetails.imageBuildingURL);
    } else if(locationDetails?.imageFloorURL){
      setImg(locationDetails.imageFloorURL);
    } else if(locationDetails?.imageLabURL){
      setImg(locationDetails.imageLabURL);
    }
    
    setLocationDetails((oldValue) => {
      return {
        ...oldValue,
        location_id: locationDetails.location_id,
        branch_id: locationDetails.branch_id,
        facility_id: locationDetails.facility_id,
        building_id: locationDetails.building_id,
        floor_id: locationDetails.floor_id,
        lab_id: locationDetails.lab_id,
      };
    });
    setProgressState((oldValue) => {
      let newValue = 0;
      if(locationDetails.lab_id){
        newValue = 6;
        setIsGeoMap(false);
        setImageState(1);
        setIsDashBoard(2);
        fetchLab();
        locationAlerts({lab_id: locationDetails.lab_id});
      } else if(locationDetails.floor_id){
        newValue = 5;
        setIsGeoMap(false);
        setImageState(1);
        fetchFloor();
        locationAlerts({floor_id: locationDetails.floor_id});
      } else if(locationDetails.building_id){
        newValue = 4;
        setIsGeoMap(false);
        setImageState(1);
        fetchBuilding();
        locationAlerts({building_id: locationDetails.building_id});
      } else if (locationDetails.facility_id) {
        newValue = 3;
        fetchFacility();
        locationAlerts({facility_id: locationDetails.facility_id});
      } else if (locationDetails.branch_id) {
        newValue = 2;
        fetchBranch();
        locationAlerts({branch_id: locationDetails.branch_id});
      } else if (locationDetails.location_id) {
        newValue = 1;
        fetchBranch();
        locationAlerts({location_id: locationDetails.location_id});
      } else {
        fetchLocation();
        locationAlerts({});
      }
      return newValue;
    });

    if(dashboardRefresh === true){
      ApplicationStore().setStorage('dashboardRefresh', {dashboardRefresh : false, labelCount: labelCount});
      setInterval(()=>{
        console.log('Interval Running..');
        let fetchedLabelCount = 0;
        let { locationLabel, branchLabel, facilityLabel, buildingLabel, floorLabel, labLabel} = ApplicationStore().getStorage('siteDetails');
        fetchedLabelCount = locationLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        fetchedLabelCount = branchLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        fetchedLabelCount = facilityLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        fetchedLabelCount = buildingLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        fetchedLabelCount = floorLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        fetchedLabelCount = labLabel !== '' ? fetchedLabelCount + 1 : fetchedLabelCount;
        if(labelCount >= fetchedLabelCount){
          location.reload();
        }
      }, 1000);
    }
  }, []);

  const fetchLocation = () => {
    FetchLocationService(handleLocationSuccess, handleBranchException);
    locationAlerts({});
  };

  const handleLocationSuccess = (dataObject) => {
    const newArray = dataObject.data ? dataObject.data.map((item) => {
      const coordinates = item.coordinates ? item.coordinates.replaceAll('"', '').split(',') : [];
      return {
        id: item.id,
        name: item.stateName,
        position: {
          lat: parseFloat(coordinates[0]),
          lng: parseFloat(coordinates[1]),
        },
      };
    })
      : [];
    setCenterLatitude(parseFloat(23.500 || newArray[0]?.position.lat));
    setCenterLongitude(parseFloat(80.500 || newArray[0]?.position.lng));
  };

  const fetchBranch = () => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    FetchBranchService({
      location_id: locationDetails.location_id,
    }, handleBranchSuccess, handleBranchException);
    locationAlerts({location_id: locationDetails.location_id});
  };

  const handleBranchSuccess = (dataObject) => {
    const newArray = dataObject.data ? dataObject.data.map((item) => {
      const coordinates = item.coordinates ? item.coordinates.replaceAll('"', '').split(',') : [];
      return {
        id: item.id,
        name: item.facilityName,
        position: {
          lat: parseFloat(coordinates[0]),
          lng: parseFloat(coordinates[1]),
        },
      };
    })
      : [];
    setCenterLatitude(parseFloat(newArray[0]?.position.lat));
    setCenterLongitude(parseFloat(newArray[0]?.position.lng));
  };

  const handleBranchException = (errorObject) => { };

  const fetchFacility = () => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    FetchFacilitiyService({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
    }, handleFetchSuccess, handleFetchException);
    locationAlerts({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
    });
  };

  const handleFetchSuccess = (dataObject) => {
    const newArray = dataObject.data ? dataObject.data.map((item) => {
      const coordinates = item.coordinates ? item.coordinates.replaceAll('"', '').split(',') : [];
      return {
        id: item.id,
        name: item.facilityName,
        position: {
          lat: parseFloat(coordinates[0]),
          lng: parseFloat(coordinates[1]),
        },
      };
    })
      : [];
    setCenterLatitude(parseFloat(newArray[0]?.position.lat));
    setCenterLongitude(parseFloat(newArray[0]?.position.lng));
  };

  const handleFetchException = (errorObject) => { };

  const fetchBuilding = () => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    BuildingFetchService({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
    }, handleBuildingSuccess, handleBuildingException);
    locationAlerts({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
    });
  };

  const handleBuildingSuccess = (dataObject) => {
    
  };

  const handleBuildingException = (errorObject) => { };

  const fetchFloor = () => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    FloorfetchService({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
      building_id: locationDetails?.building_id,

    }, handleFloorSuccess, handleFloorException);
    locationAlerts({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
      building_id: locationDetails?.building_id,
    });
  };

  const handleFloorSuccess = (dataObject) => {
  };

  const handleFloorException = (errorObject) => { };

  const fetchLab = () => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    LabfetchService({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
      building_id: locationDetails?.building_id,
      floor_id: locationDetails?.floor_id,
    }, handleLabSuccess, handleLabException);
    locationAlerts({
      location_id: locationDetails?.location_id,
      branch_id: locationDetails?.branch_id,
      facility_id: locationDetails?.facility_id,
      building_id: locationDetails?.building_id,
      floor_id: locationDetails?.floor_id,
    });
  };

  const handleLabSuccess = (dataObject) => {
  };

  const handleLabException = (errorObject) => { };

  const locationAlerts = (alertLocationDetails) =>{
    DeviceIdAlerts(alertLocationDetails, handleSuccessAlerts, handleExceptionAlerts);
  }

  const handleSuccessAlerts = (dataObject) => {  
    setAlertList(dataObject.data || []);
  }

  const handleExceptionAlerts = () => { };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  
  return (
    <Grid container spacing={1} className={'h-full w-full p-0.5 ml-0 mt-0'}>
    {isdashboard === 0 && (
      <div className={'h-auto w-full'}>
        <Grid item xs={12} sx={{ marginLeft: 1, }} className={'h-auto w-full ml-0'}>
          <Grid container item xs={12} sm={12} md={12} lg={12}  className={'h-auto w-full'}>
            <Grid item xs={12} sm={12} md={8} lg={8} sx={{}} className={'min-h-[300px] h-auto'}>
              <LocationGridWidget
                setLocationCoordinationList={setLocationCoordinationList}
                locationState={locationState}
                setProgressState={setProgressState}
                setImg={setImg}
                setImageState={setImageState}
                locationDetails={locationDetails}
                setLocationDetails={setLocationDetails}
                setDeviceCoordsList={setDeviceCoordsList}
                setIsDashBoard={setIsDashBoard}
                setIsGeoMap={setIsGeoMap}
                siteImages={siteImages}
                setSiteImages={setSiteImages}
                setZoomLevel={setZoomLevel}
                setCenterLatitude={setCenterLatitude}
                setCenterLongitude={setCenterLongitude}
                breadCrumbLabels={breadCrumbLabels}
                setBreadCrumbLabels={setBreadCrumbLabels}
                setAlertList={setAlertList}
                locationAlerts={locationAlerts}
              />
            </Grid>
            <Grid item xs={12} sm={12} md={4} lg={4} className={' h-[45vh] p-6 xl:h-[40vh]'} >
              {/* eslint-disable-next-line */}
              {isGeoMap === true ? <GeoLocationWidget locationCoordination={locationCoordinationList} zoomLevel={zoomLevel} centerLatitude={centerLatitude} centerLongitude={centerLongitude} />
                : <ImageMarkerList labImage={imgSrc} deviceCoordsList={deviceCoordsList} height="h-[40vh]" />}
            </Grid>
            <Grid item xs={12} sm={12} md={12} className={'p-6 h-[40vh] pt-5'}>
              <AlertWidget dataList={alertList} setAlertList={setAlertList} setNotification={setNotification} />
            </Grid>
          </Grid>
        </Grid>
      </div>
    )}
    {isdashboard === 1 && (
      <div className={'h-[94vh] w-full'}>
        <LandingPageComponent locationDetails={locationDetails} setIsDashBoard={setIsDashBoard} />
      </div>
    )}
    {isdashboard === 2 && (
      <div className={'h-[auto] w-full overflow-auto '}>
        <DeviceGridComponent
          setImg={setImg}
          locationDetails={locationDetails}
          setLocationDetails={setLocationDetails}
          setDeviceCoordsList={setDeviceCoordsList}
          setProgressState={setProgressState}
          breadCrumbLabels={breadCrumbLabels}
          setBreadCrumbLabels={setBreadCrumbLabels}
          setIsDashBoard={setIsDashBoard}
          setIsGeoMap={setIsGeoMap}
          siteImages={siteImages}
          setSiteImages={setSiteImages}
          setCenterLatitude={setCenterLatitude}
          setCenterLongitude={setCenterLongitude}
          locationAlerts={locationAlerts}
        />
      </div>
    )}
    <NotificationBar
      handleClose={handleClose}
      notificationContent={openNotification.message}
      openNotification={openNotification.status}
      type={openNotification.type}
    />
  </Grid>
  );
}

export default Dashboard;
