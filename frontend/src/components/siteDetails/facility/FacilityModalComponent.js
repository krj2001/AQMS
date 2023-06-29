import {
  Button, Dialog, DialogContent, DialogTitle, TextField, Grid,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import { FacilitiyAddService, FacilityEditService } from '../../../services/LoginPageService';
import MapsComponent from '../../maps/googleMapsComponent';
import { FacilityAddFormValidate } from '../../../validation/locationValidation';

import NotificationBar from '../../notification/ServiceNotificationBar';

function FacilityModal({
  open, setOpen, isAddButton, editData, locationId, branchId, setRefreshData, locationCoordinationList, centerCoord
}) {
  const [facilityName, setFacilityName] = useState('');
  const [longitude, setLongitude] = useState('');
  const [latitude, setLatitude] = useState('');
  const [location_id, setLocationId] = useState(locationId);
  /* eslint-disable-next-line */
  const [branch_id, setBranchId] = useState(branchId);
  const [facilityId, setFacilityId] = useState('');
  const [errorObject, setErrorObject] = useState({});

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const [markerLat, setMarkerLat] = useState(0);
  const [markerLng, setMarkerLng] = useState(0);

  useEffect(() => {
    if (editData) {
      setOpen(open);
      loaddata();
    }
  }, [editData]);

  const loaddata = () => {
    const coordinates = editData.coordinates ? editData.coordinates.split(',') : ['', ''];
    setFacilityName(editData.facilityName || '');
    setLatitude(coordinates[0]);
    setLongitude(coordinates[1]);
    setFacilityId(editData.id || '');
    setMarkerLng(parseFloat(coordinates[0]));
    setMarkerLat(parseFloat(coordinates[1]));
  };
  /* eslint-disable-next-line */
  const clearForm = () => {
    setFacilityName('');
    setLongitude('');
    setLatitude('');
    setLocationId('');
  };
  const handleSubmit = async (e) => {
    e.preventDefault();
    if (longitude === '' || latitude === '') {
      setErrorObject((oldErrorState) => {
        let status = {};
        status = {
          errorStatus: true,
          helperText: 'Please choose the points in Map',
        };
        return {
          ...oldErrorState,
          coordinates: status,
        };
      });
    } else {
      const coordinates = JSON.stringify(`${latitude},${longitude}`).replaceAll('"', '');
      if (isAddButton) {
        await FacilitiyAddService({
          facilityName, coordinates, location_id, branch_id,
        }, handleSuccess, handleException);
      } else {
        await FacilityEditService({
          facilityName, coordinates, location_id, branch_id, facilityId,
        }, handleSuccess, handleException);
      }
    }
  };

  const handleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setOpen(false);
      setErrorObject({});
    }, 5000);
  };
  /* eslint-disable-next-line */
  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setErrorObject({});
  };
  const onMapClick = (e) => {
    delete errorObject.coordinates;
    setLatitude(e.latLng.lat());
    setLongitude(e.latLng.lng());
  };
  const validateForNullValue = (value, type) => {
    FacilityAddFormValidate(value, type, setErrorObject);
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { minWidth: '80%' } }}
      maxWidth="sm"
      open={open}
    >
      <DialogTitle
        sx={{ fontFamily: 'customfont', fontSize: '20px', textAlign: 'center', fontWeight: '600', margin: '20px 0', letterSpacing: '1px' }}>
        {isAddButton ? 'Add Facility' : 'Edit Facility'}
      </DialogTitle>
      <DialogContent>
        <form className="mt-2 space-y-6" onSubmit={handleSubmit}>
          <div className="rounded-md  -space-y-px ">

            <div className="container mx-auto outline-black">
              <div className="flex flex-col w-full">
                <div className="w-full flex sm:float-left gap-5 pr-3 pl-3 mb-5 min-[320px]:flex-col min-[768px]:flex-row">
                  <div className="rounded-md -space-y-px mb-2 w-full">
                    <TextField
                      fullWidth
                      sx={{ mb: 1, width: '100%' }}
                      label="Location Name"
                      type="text"
                      value={facilityName}
                      variant="outlined"
                      placeholder="Please enter location name"
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                      placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(facilityName, 'facilityName')}
                      onChange={(e) => { setFacilityName(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.facilityName?.errorStatus}
                      helperText={errorObject?.facilityName?.helperText}
                      InputLabelProps={{
                        shrink: true,
                        style: { fontFamily: 'customfont' }
                      }}
                    />
                  </div>
                  <div className="rounded-md -space-y-px mb-2 w-full">
                    <TextField
                      fullWidth
                      sx={{ mb: 1 }}
                      label="Latitude"
                      type="text"
                      disabled
                      value={latitude}
                      variant="outlined"
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                      placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                      required
                      onChange={(e) => { setLatitude(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.coordinates?.errorStatus}
                      helperText={errorObject?.coordinates?.helperText}
                      InputLabelProps={{
                        shrink: true,
                        style: { fontFamily: 'customfont' }
                      }}
                    />
                  </div>
                  <div className="rounded-md -space-y-px mb-2 w-full">
                    <TextField
                      fullWidth
                      sx={{ mb: 1 }}
                      label="Longitude"
                      type="text"
                      disabled
                      value={longitude}
                      variant="outlined"
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                      placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                      required
                      onChange={(e) => { setLongitude(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.coordinates?.errorStatus}
                      helperText={errorObject?.coordinates?.helperText}
                      InputLabelProps={{
                        shrink: true,
                        style: { fontFamily: 'customfont' }
                      }}
                    />
                  </div>
                </div>
                <div className="w-10/12 mr-auto ml-auto sm:float-right lg:float-left  pr-1 mb-7 min-[320px]:w-full min-[768px]:w-[80%]">
                  <Grid item xs={4} sm={4} md={4} lg={4} />
                  <MapsComponent
                    onMarkerDrop={onMapClick}
                    height="50vh"
                    width="100%"
                    longitude={markerLng}
                    latitude={markerLat}
                    stateName={editData.facilityName}
                    zoom={10}
                    center={isAddButton ? { lat: centerCoord.lat, lng: centerCoord.lng }
                      : {
                        lat: Number(latitude) || 80.500,
                        lng: Number(longitude) || 23.500,
                      }}
                    flagDistance={0.03}
                  />
                </div>
              </div>
            </div>
            <div className="float-right">
              <div className="rounded-md -space-y-px pb-10 min-[320px]:pb-10 min-[768px]:pb-0">
                <Button
                  type="submit"
                 style={{
                  background: 'rgb(19 60 129)',}}
                  sx={{
                    color: 'white',
                    padding: "8px 30px",
                    marginRight: '30px',
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    boxShadow: 'none',
                    "&.Mui-disabled": {
                      background: "#eaeaea",
                      color: "#c0c0c0"
                    }
                  }}
                  disabled={errorObject?.coordinates?.errorStatus || errorObject?.stateName?.errorStatus}
                >
                  {isAddButton ? 'Add' : 'Update'}
                </Button>
                <Button
                  style={{
                    background: 'rgb(19 60 129)',}}
                  sx={{
                    color: 'white',
                    padding: "8px 19px",
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    boxShadow: 'none'
                  }}
                  onClick={() => {
                    setOpen(false);
                    setErrorObject({});
                    loaddata();
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </div>
        </form>
      </DialogContent>

      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </Dialog>
  );
}

export default FacilityModal;
