import { Breadcrumbs, Card, CardHeader, CardContent, Chip, Typography, Paper } from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import React, { useEffect, useState } from 'react';
import { FetchFacilitiyService } from '../../../../services/LoginPageService';
import { setAlertPriorityAndType, setAQIColor, setAQILabel } from '../../../../utils/helperFunctions';
import ApplicationStore from '../../../../utils/localStorageUtil';
import { MdLocationPin } from 'react-icons/md'
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
/* eslint-disable radix */
/* eslint-disable no-shadow */
/* eslint-disable no-nested-ternary */
/* eslint-disable array-callback-return */

function FacilityGridComponent(props) {
  const { setLocationDetails, setProgressState, breadCrumbLabels, setBreadCrumbLabels,
    setLocationCoordinationList, setIsGeoMap, setDeviceCoordsList,
    setZoomLevel, setCenterLatitude, setCenterLongitude, setAlertList, locationAlerts
  } = props;
  const { facilityIdList } = ApplicationStore().getStorage('alertDetails');
  const [isLoading, setGridLoading] = useState(true);
  const facilityColumns = [
    {
      field: 'facilityName',
      headerName: 'Facility Name',
      minWidth: 200,
      flex: 1,
      type: 'actions',
      renderCell: ((params) => {
        return (
          <>
            <div className='flex w-full justify-between'>
              <div>
              <MdLocationPin className='text-[18px] text-left w-full' />
              </div>
             <div className='w-full'>
             <LinkTo selectedRow={params.row} />
             </div>
             
            </div>

          </>
        )
      }),
      getActions: (params) => [
        <LinkTo selectedRow={params.row} />,
      ],
    },
    {
      field: 'id',
      headerName: 'Status',
      minWidth: 120,
      flex: 1,
      headerAlign: 'center',
      align: 'center',
      fontFamily: 'customfont',
      renderCell: ((params) => {
        let element = {
          alertLabel: 'Good',
          alertColor: 'green',
          alertPriority: 6,
        };
        const alertObject = facilityIdList?.filter((alert) => {
          return params.row.id === parseInt(alert.id);
        });

        alertObject?.map((data) => {
          element = setAlertPriorityAndType(element, data);
        });

        return (
          <Chip
            className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={element.alertLabel}
            style={{
              color: 'white',
              fontWeight:'600',
              borderColor: element.alertColor,
              background: element.alertColor,
            }}
          />
        );
      }),
    },
    {
      field: 'aqiIndex',
      headerName: 'AQI Value',
      minWidth: 120,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      fontFamily: 'customfont',
      renderCell: ((params) => {
        return (
          <Chip
            className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={setAQILabel(params.row.aqiIndex.replaceAll(",", ""))}
            style={{
              color: setAQIColor(params.row.aqiIndex),
              borderColor: setAQIColor(params.row.aqiIndex),
            }}
          />
        )
      }),
    },
    {
      // field: 'aqiIndex',
      headerName: 'AQI Category',
      minWidth: 120,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      fontFamily: 'customfont',
      renderCell: ((params) => {
        return (
          <Chip
            className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={setAQILabel(params.row.aqiIndex.replaceAll(",", ""))}
            style={{
              color: 'white',
              borderColor: setAQIColor(params.row.aqiIndex),
              background: setAQIColor(params.row.aqiIndex),
            }}
          />
        )
      }),
    }
  ];

  const [dataList, setDataList] = useState([]);

  useEffect(() => {
    setGridLoading(true);
    FetchFacilitiyService({
      location_id: props.locationDetails.location_id,
      branch_id: props.locationDetails.branch_id,
    }, handleSuccess, handleException);
  }, [props.locationDetails]);

  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setDataList(dataObject.data);
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
    setLocationCoordinationList(newArray);
    setZoomLevel(9);
  };

  const handleException = () => { };

  function LinkTo({ selectedRow }) {
    return (
      <h3 className='text-sm font-[customfont] font-medium cursor-pointer '
        onClick={() => {
          locationAlerts({ facility_id: selectedRow.id });
          setLocationDetails((oldValue) => {
            return { ...oldValue, facility_id: selectedRow.id };
          });

          setBreadCrumbLabels((oldvalue) => {
            return { ...oldvalue, facilityLabel: selectedRow.facilityName };
          });

          setProgressState(3);
          const coordList = selectedRow.coordinates.replaceAll('"', '').split(',') || [];
          // setCenterLatitude(parseFloat(coordList[0]));
          // setCenterLongitude(parseFloat(coordList[1]));
          setCenterLatitude('');
          setCenterLongitude('');
        }}
      >
        {selectedRow.facilityName}
      </h3>
    );
  }

  const setLocationlabel = (value) => {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    setProgressState((oldValue) => {
      let newValue = value;
      if (locationDetails.lab_id) {
        newValue = value < 7 ? 6 : value;
      } else if (locationDetails.floor_id) {
        newValue = value < 6 ? 5 : value;
      } else if (locationDetails.building_id) {
        newValue = value < 5 ? 4 : value;
      } else if (locationDetails.facility_id) {
        newValue = value < 4 ? 3 : value;
      } else if (locationDetails.branch_id) {
        newValue = value < 3 ? 2 : value;
      } else if (locationDetails.location_id) {
        newValue = value < 2 ? 1 : value;
      } else {
        // locationAlerts({});
      }
      return newValue;
    });
  };

  return (
    <>
      <Card className={'h-[48vh] sm:h-[40vh] xl:h-[35vh]'} sx={{ boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius: '12px' }}>
        <Paper elevation={3} className={'h-full'} style={{ boxShadow: 'none' }}>
          <CardHeader
            title={
              <Breadcrumbs aria-label="breadcrumb" separator="›" fontSize='20px' fontWeight='600' >
                <h3 className='font-[customfont] font-[600] tracking-[1px] p-1 text-black text-[15px] cursor-pointer'
                  onClick={() => {
                    const { locationDetails } = ApplicationStore().getStorage('userDetails');
                    let value = 0;
                    if (locationDetails.lab_id) {
                      locationAlerts({ lab_id: locationDetails.lab_id || props.locationDetails.lab_id });
                      value = 6;
                    } else if (locationDetails.floor_id) {
                      locationAlerts({ floor_id: locationDetails.floor_id || props.locationDetails.floor_id });
                      value = 5;
                    } else if (locationDetails.building_id) {
                      locationAlerts({ building_id: locationDetails.building_id || props.locationDetails.building_id });
                      value = 4;
                    } else if (locationDetails.facility_id) {
                      locationAlerts({ facility_id: locationDetails.facility_id || props.locationDetails.facility_id });
                      value = 3;
                    } else if (locationDetails.branch_id) {
                      locationAlerts({ branch_id: locationDetails.branch_id || props.locationDetails.branch_id });
                      value = 2;
                    } else if (locationDetails.location_id) {
                      locationAlerts({ location_id: locationDetails.location_id || props.locationDetails.location_id });
                      value = 1;
                    } else {
                      locationAlerts({});
                      value = 0;
                    }
                    setLocationlabel(value);
                    setDeviceCoordsList([]);
                    // setCenterLatitude(23.500);
                    // setCenterLongitude(80.000);
                    setIsGeoMap(true);
                  }}
                >
                  Location
                </h3>
                <h3 className='font-[customfont] font-[600] tracking-[1px] p-1 text-black text-[14.5px] cursor-pointer'
                  onClick={() => {
                    const { locationDetails } = ApplicationStore().getStorage('userDetails');
                    let value = 1;
                    if (locationDetails.lab_id) {
                      locationAlerts({ lab_id: locationDetails.lab_id || props.locationDetails.lab_id });
                      value = 6;
                    } else if (locationDetails.floor_id) {
                      locationAlerts({ floor_id: locationDetails.floor_id || props.locationDetails.floor_id });
                      value = 5;
                    } else if (locationDetails.building_id) {
                      locationAlerts({ building_id: locationDetails.building_id || props.locationDetails.building_id });
                      value = 4;
                    } else if (locationDetails.facility_id) {
                      locationAlerts({ facility_id: locationDetails.facility_id || props.locationDetails.facility_id });
                      value = 3;
                    } else if (locationDetails.branch_id) {
                      locationAlerts({ branch_id: locationDetails.branch_id || props.locationDetails.branch_id });
                      value = 2;
                    } else {
                      locationAlerts({ location_id: locationDetails.location_id || props.locationDetails.location_id });
                      value = 1;
                    }
                    setLocationlabel(value);
                    setDeviceCoordsList([]);
                    setIsGeoMap(true);
                  }}
                >
                  {breadCrumbLabels.stateLabel}
                </h3>
                <Typography
                  underline="hover"
                  color="black"
                  fontFamily={'customfont'}
                  fontWeight={'600'}
                  fontSize={'14px'}
                  letterSpacing={'1px'}
                >
                  {breadCrumbLabels.branchLabel}
                </Typography>
              </Breadcrumbs>
            }
            sx={{ paddingBottom: 0 }}
          />
          <CardContent className={'h-[81%] sm:h-[90%]'}>
            <DataGrid
              sx={{ fontFamily: 'customfont' }}
              rows={dataList}
              columns={facilityColumns}
              loading={isLoading}
              pageSize={3}
              rowsPerPageOptions={[3]}
              disableSelectionOnClick
              style={{
                border: 'none',
                // maxHeight: `${80}%`,
              }}
            />
          </CardContent>
        </Paper>
      </Card>
    </>
  );
}

export default FacilityGridComponent;
