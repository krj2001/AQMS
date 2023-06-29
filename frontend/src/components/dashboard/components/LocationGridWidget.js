import React, { useEffect } from 'react';
import LocationGridComponent from '../subComponent/siteDetailsComponent/LocationGridComponent';
import BranchGridComponent from '../subComponent/siteDetailsComponent/BranchGridComponent';
import BuildingGridComponent from '../subComponent/siteDetailsComponent/BuildingGridComponent';
import FacilityGridComponent from '../subComponent/siteDetailsComponent/FacilityGridComponent';
import FloorGridComponent from '../subComponent/siteDetailsComponent/FloorGridComponent';
import LabGridComponent from '../subComponent/siteDetailsComponent/LabGridComponent';
import ApplicationStore from '../../../utils/localStorageUtil';
/* eslint-disable max-len */
function LocationGridWidget({
  locationDetails, setLocationDetails, locationState, setProgressState, setImageState, setImg,
  setDeviceCoordsList, setLocationCoordinationList, setIsDashBoard, setIsGeoMap, siteImages, setSiteImages,
  setZoomLevel, setCenterLatitude, setCenterLongitude, breadCrumbLabels, setBreadCrumbLabels, setAlertList,
  locationAlerts
}) {
  const { newNotification } = ApplicationStore().getStorage('notificationDetails');

  useEffect(() => {
    if (locationState === 4 || locationState === 5 || locationState === 6) {
      setImageState(1);
    }
  }, [locationState]);
  return (
    <div  className={'h-auto p-6'}>
      {
        locationState === 0
          ? (
            <LocationGridComponent
              setAlertList={setAlertList}
              newNotification={newNotification}
              setLocationCoordinationList={setLocationCoordinationList}
              setLocationDetails={setLocationDetails}
              setProgressState={setProgressState}
              breadCrumbLabels={breadCrumbLabels}
              setBreadCrumbLabels={setBreadCrumbLabels}
              setZoomLevel={setZoomLevel}
              setCenterLatitude={setCenterLatitude}
              setCenterLongitude={setCenterLongitude}
              locationAlerts={locationAlerts}
            />
          ) : ''
      }
      {
        locationState === 1
          ? (
            <BranchGridComponent
              setAlertList={setAlertList}
              setLocationCoordinationList={setLocationCoordinationList}
              locationDetails={locationDetails}
              setLocationDetails={setLocationDetails}
              setProgressState={setProgressState}
              breadCrumbLabels={breadCrumbLabels}
              setBreadCrumbLabels={setBreadCrumbLabels}
              setIsGeoMap={setIsGeoMap}
              setDeviceCoordsList={setDeviceCoordsList}
              setZoomLevel={setZoomLevel}
              setCenterLatitude={setCenterLatitude}
              setCenterLongitude={setCenterLongitude}
              locationAlerts={locationAlerts}
            />
          ) : ''
      }
      {
        locationState === 2
          ? (
            <FacilityGridComponent
              setAlertList={setAlertList}
              setLocationCoordinationList={setLocationCoordinationList}
              locationDetails={locationDetails}
              setLocationDetails={setLocationDetails}
              setProgressState={setProgressState}
              breadCrumbLabels={breadCrumbLabels}
              setBreadCrumbLabels={setBreadCrumbLabels}
              setIsGeoMap={setIsGeoMap}
              setDeviceCoordsList={setDeviceCoordsList}
              setZoomLevel={setZoomLevel}
              setCenterLatitude={setCenterLatitude}
              setCenterLongitude={setCenterLongitude}
              locationAlerts={locationAlerts}
            />
          ) : ''
      }
      {
        locationState === 3
          ? (
            <BuildingGridComponent
              setAlertList={setAlertList}
              setImg={setImg}
              setLocationCoordinationList={setLocationCoordinationList}
              locationDetails={locationDetails}
              setLocationDetails={setLocationDetails}
              setProgressState={setProgressState}
              breadCrumbLabels={breadCrumbLabels}
              setBreadCrumbLabels={setBreadCrumbLabels}
              setIsGeoMap={setIsGeoMap}
              setDeviceCoordsList={setDeviceCoordsList}
              siteImages={siteImages}
              setSiteImages={setSiteImages}
              setZoomLevel={setZoomLevel}
              setCenterLatitude={setCenterLatitude}
              setCenterLongitude={setCenterLongitude}
              locationAlerts={locationAlerts}
            />
          ) : ''
      }
      {
        locationState === 4
          ? (
            <FloorGridComponent
              setAlertList={setAlertList}
              setImg={setImg}
              locationDetails={locationDetails}
              setLocationDetails={setLocationDetails}
              setProgressState={setProgressState}
              breadCrumbLabels={breadCrumbLabels}
              setBreadCrumbLabels={setBreadCrumbLabels}
              setIsGeoMap={setIsGeoMap}
              setDeviceCoordsList={setDeviceCoordsList}
              siteImages={siteImages}
              setSiteImages={setSiteImages}
              setCenterLatitude={setCenterLatitude}
              setCenterLongitude={setCenterLongitude}
              locationAlerts={locationAlerts}
            />
          ) : ''
      }
      {locationState === 5
        ? (
          <LabGridComponent
            setAlertList={setAlertList}
            setImg={setImg}
            locationDetails={locationDetails}
            setLocationDetails={setLocationDetails}
            setProgressState={setProgressState}
            breadCrumbLabels={breadCrumbLabels}
            setBreadCrumbLabels={setBreadCrumbLabels}
            setIsDashBoard={setIsDashBoard}
            setIsGeoMap={setIsGeoMap}
            setDeviceCoordsList={setDeviceCoordsList}
            siteImages={siteImages}
            setSiteImages={setSiteImages}
            setCenterLatitude={setCenterLatitude}
            setCenterLongitude={setCenterLongitude}
            locationAlerts={locationAlerts}
          />
        ) : ''}
    </div>
  );
}

export default LocationGridWidget;
