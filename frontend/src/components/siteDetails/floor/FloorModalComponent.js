import {
  Button, Dialog, DialogContent, DialogTitle, TextField, Grid,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import { FloorAddService, FloorEditService } from '../../../services/LoginPageService';
import { LocationFormValidate } from '../../../validation/locationValidation';
import NotificationBar from '../../notification/ServiceNotificationBar';
import ImageMarkerComponent from './imageMarker';
import previewImage from '../../../images/previewImage.png';

function FloorModal({
  open, setOpen, isAddButton, editData, locationId, branchId, facilityId, buildingId, setRefreshData, src,
}) {
  const [floorName, setFloorName] = useState('');
  const [floorStage, setFloorStage] = useState(0);
  const [floorMap, setFloorMap] = useState({});
  const [floorCords, setFloorCords] = useState('');
  const [location_id, setLocationId] = useState(locationId);
  const [branch_id, setBranchId] = useState(branchId);
  const [facility_id, setFacilityId] = useState(facilityId);
  const [building_id, setBuildingId] = useState(buildingId);
  const [floor_id, setFloorId] = useState('');
  const [previewFloor, setPreviewFloor] = useState('');
  const [errorObject, setErrorObject] = useState({});
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    if (editData) {
      setOpen(open);
      loaddata();
    }
  }, [editData]);

  const loaddata = () => {
    setFloorName(editData.floorName || '');
    setFloorId(editData.id || '');
    setFloorStage(editData.floorStage || '');
    setFloorCords(editData.floorCords || '');
    //setPreviewFloor(editData.floorMap ? `https://localhost/backend/blog/public/${editData.floorMap}` : previewImage);
    setPreviewFloor(editData.floorMap ? `${process.env.REACT_APP_API_ENDPOINT}blog/public/${editData.floorMap}` : previewImage);
    // setFloorMap(editData.floorMap || '');
    // setFloorCords(editData.floorCords || []);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    // if(longitude == '' || latitude == ''){
    //     setErrorObject(oldErrorState => {
    //         let status = {}
    //         status = {
    //             errorStatus: true,
    //             helperText: 'Please choose the points in Map'
    //         }
    //         return {
    //             ...oldErrorState,
    //             coordinates: status
    //         }
    //     });
    // }
    // else{
    if (isAddButton) {
      // alert(floorCords+"\n"+floorName+"\n"+floorStage +"\n"+location_id+"\n"+branch_id+"\n"+facility_id+"\n"+building_id);
      await FloorAddService({
        floorName, floorStage, floorMap, floorCords, location_id, branch_id, facility_id, building_id,
      }, handleSuccess, handleException);
    } else {
      await FloorEditService({
        floorName, floorStage, floorMap, floorCords, location_id, branch_id, facility_id, building_id, floor_id,
      }, handleSuccess, handleException);
    }
    // }
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
    // setLongitude(e.latLng.lat());
    // setLatitude(e.latLng.lng());
  };
  const validateForNullValue = (value, type) => {
    LocationFormValidate(value, type, setErrorObject);
  };

  const setFloorCoordinations = (value, direction) => {
    const cord = `${value.top},${value.left},${direction}`;
    setFloorCords(cord); // Error not yet solved
    // setFloorCords(oldArray => [...oldArray,direction]); // Error not yet solved

    // let cord = value.top+","+value.left;
    // setFloorCords(cord); // Error not yet solved
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const processImageFile = (imageFile) => {
    if(imageFile){
      if (imageFile.type.match('image/jpeg') || imageFile.type.match('image/png')) {
      // Success phase
    } else {
      setNotification({
        status: true,
        type: 'error',
        message: 'Please select an image (Supported type : .jpeg/.png)',
      });
    }
    }
  }

  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { minWidth: '80%' },  }}
      maxWidth="sm"
      open={open}
    >
      <DialogTitle
        sx={{ fontFamily: 'customfont', fontSize: '20px', textAlign: 'center', fontWeight: '600', margin: '10px 0', letterSpacing: '1px' }}>
        {isAddButton ? 'Add Floor' : 'Edit Floor'}
      </DialogTitle>
      <DialogContent>
        <form className="mt-2 space-y-6" onSubmit={handleSubmit}>
          <div className="rounded-md  -space-y-px ">
            {/* <div className='rounded-md -space-y-px mb-2'>

                    </div> */}
            <div className="container mx-auto outline-black">
              <div className="flex flex-col w-full">
                <div className="w-full flex  gap-3 mb-5 min-[320px]:flex-col min-[768px]:flex-row">
                  <div className="rounded-md -space-y-px mb-2 w-full">
                    <TextField
                      fullWidth
                      sx={{ mb: 1 }}
                      label="Floor Name"
                      type="text"
                      value={floorName}
                      variant="outlined"
                      // placeholder="Please enter Floor name"
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(floorName, 'buildingName')}
                      onChange={(e) => { setFloorName(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.buildingName?.errorStatus}
                      helperText={errorObject?.buildingName?.helperText}
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
                      label="Floor number"
                      type="number"
                      value={floorStage}
                      variant="outlined"
                      // placeholder="Please enter Floor number"
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(floorStage, 'buildingTotalFloors')}
                      onChange={(e) => { setFloorStage(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.buildingTotalFloors?.errorStatus}
                      helperText={errorObject?.buildingTotalFloors?.helperText}
                      InputLabelProps={{
                        shrink: true,
                        style: { fontFamily: 'customfont' }
                      }}
                    />
                  </div>

                </div>
                <div className='flex  w-full gap-5 min-[320px]:flex-col min-[768px]:flex-row-reverse mb-5' >
                  <div className='w-[80%] h- min-[320px]:w-[100%] min-[768px]:w-[80%]'>
                    <div className="rounded-md -space-y-px mb-7 w-full ">
                      <TextField
                        fullWidth
                        label="Floor Image"
                        required={!!isAddButton}
                        onBlur={() => {
                          validateForNullValue(floorMap, 'buildingImg');
                        }}
                        onChange={(e) => {
                          // setCustomerLogo(e.target.files);
                          if (e.target.files && e.target.files.length > 0) {
                            setFloorMap(e.target.files[0]);
                            processImageFile(e.target.files[0]);
                            // const reader = new FileReader();
                            // reader.readAsDataURL(e.target.files[0]);
                            const reader = new FileReader();
                            reader.onload = () => {
                              if (reader.readyState === 2) {
                                setFloorMap(reader.result);
                                setPreviewFloor(reader.result);
                                // setImgdata(reader.result);
                              }
                            };
                            reader.readAsDataURL(e.target.files[0]);
                          }
                        }}
                        InputLabelProps={{ shrink: true }}
                        type="file"
                        inputProps={{
                          accept: 'image/png, image/jpeg',
                        }}
                        error={errorObject?.buildingImg?.errorStatus}
                        helperText={errorObject?.buildingImg?.helperText}
                      />
                    </div>
                    <div className="rounded-md -space-y-px mb-2" 
                    // style={{ border: '1px solid #b7b7b7' }}
                    >
                      <img src={previewFloor || previewImage} 
                      // style={{ width: '-webkit-fill-available' }} 
                      className='h-[40vh] w-full object-contain'
                      />
                    </div>
                  </div>
                  <div className="w-5/12  pr-1 min-[320px]:w-full min-[768px]:w-5/12">
                    <Grid item xs={4} sm={4} md={4} lg={4} />
                    {/* <MapsComponent
                                    height = '70vh'
                                    width = '100%'
                                    onMarkerDrop={onMapClick}
                                    longitude = {markerLng}
                                    latitude = {markerLat}
                                    stateName ={editData.buildingName}
                                /> */}

                    <ImageMarkerComponent
                      src={src}
                      width="500px"
                      setFloorCoordinations={setFloorCoordinations}
                      floorCords={floorCords}
                      isAddButton={isAddButton}
                    />

                  </div>
                </div>

              </div>
            </div>
          </div>
          <div className="float-right py-5" style={{ marginTop: '-15px' }}>
            <div className="min-[320px]:mb-10">
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 1,
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
                type="submit"
                size="large"
                disabled={errorObject?.buildingName?.errorStatus || errorObject?.buildingTotalFloors?.errorStatus}
              >
                {isAddButton ? 'Add' : 'Update'}
              </Button>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 1,
                  color: 'white',
                  padding: "8px 19px",
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  boxShadow: 'none'
                }}
                size="large"
                onClick={(e) => {
                  setOpen(false);
                  setErrorObject({});
                  loaddata();
                }}
              >
                Cancel
              </Button>
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
    </Dialog >
  );
}

export default FloorModal;
