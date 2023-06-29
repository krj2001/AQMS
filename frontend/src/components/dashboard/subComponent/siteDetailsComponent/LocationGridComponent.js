import { Box, Breadcrumbs, Card, CardContent, CardHeader, Chip, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TableRow } from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import { darken, lighten } from '@mui/material/styles';
import React, { useEffect, useState } from 'react';
import { FetchLocationService } from '../../../../services/LoginPageService';
import ApplicationStore from '../../../../utils/localStorageUtil';
import { setAlertPriorityAndType, setAQIColor, setAQILabel } from '../../../../utils/helperFunctions';
import { LatestAlertAccess } from '../../../../context/UserAccessProvider';
import { MdLocationPin } from 'react-icons/md';
import './LocationGridComponent.css';

/* eslint-disable no-unused-vars */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
/* eslint-disable radix */
function LocationGridComponent(props) {
  const {
    setLocationDetails, setProgressState, setBreadCrumbLabels, setLocationCoordinationList,
    setZoomLevel, setCenterLatitude, setCenterLongitude, locationAlerts
  } = props;
  const [dataList, setDataList] = useState([]);
  const { locationIdList } = ApplicationStore().getStorage('alertDetails');
  const [notificationStatus, setNotificationStatus] = useState(locationIdList);
  const [isLoading, setGridLoading] = useState(true);
  const {alertStatus, setAlertStatus} = LatestAlertAccess();
  const columns = [

    {
      field: 'stateName',
      headerName: 'Location Name',

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
      align: 'center',
      headerAlign: 'center',
      renderCell: ((params) => {
        let element = {
          alertLabel: 'Good',
          alertColor: 'green',
          alertPriority: 6,
        };
        const alertObject = notificationStatus?.filter((alert) => {
          return params.row.id === parseInt(alert.id);
        });

        alertObject?.map((data) => {
          element = setAlertPriorityAndType(element, data);
        });

        return (
          <>
            <Chip
              className='w-[120px] font-[customfont] font-normal text-sm'
              variant="outlined"
              label={element.alertLabel}
              sx={{
                // color: element.alertColor,
                color: 'white',
                fontWeight:'600',
                borderColor: element.alertColor,
                background: element.alertColor,
              }}
            />
          </>
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
      renderCell: ((params) => {
        return (
          <Chip
            className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={setAQILabel(params.row.aqiIndex.replaceAll(",", ""))}
            sx={{
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
      renderCell: ((params) => {
        return (
          <Chip
            className='w-[120px] font-[customfont] font-normal text-sm'
            variant="outlined"
            label={setAQILabel(params.row.aqiIndex.replaceAll(",", ""))}
            sx={{
              color: 'white',
              borderColor: setAQIColor(params.row.aqiIndex),
              background: setAQIColor(params.row.aqiIndex),

            }}
          />
        )
      }),
    }
  ];

  useEffect(() => {
    setGridLoading(true);
    FetchLocationService(handleSuccess, handleException);
    const { locationDetails } = ApplicationStore().getStorage('userDetails');

    setProgressState((oldValue) => {
      let newValue = 0;
      if (locationDetails.lab_id) {
        newValue = 6;
        locationAlerts({ lab_id: locationDetails.lab_id });
      }
      else if (locationDetails.floor_id) {
        newValue = 5;
        locationAlerts({ floor_id: locationDetails.floor_id });
      }
      else if (locationDetails.building_id) {
        newValue = 4;
        locationAlerts({ building_id: locationDetails.building_id });
      }
      else if (locationDetails.facility_id) {
        newValue = 3;
        locationAlerts({ facility_id: locationDetails.facility_id });
      }
      else if (locationDetails.branch_id) {
        newValue = 2;
        locationAlerts({ branch_id: locationDetails.branch_id });
      }
      else if (locationDetails.location_id) {
        newValue = 1;
        locationAlerts({ location_id: locationDetails.location_id });
      }
      else {
        locationAlerts({});
      }
      return newValue;
    });
  }, []);

  function LinkTo({ selectedRow }) {
    const { locationDetails } = ApplicationStore().getStorage('userDetails');
    return (
      <h3
        className='text-sm font-[customfont] font-medium cursor-pointer'
        onClick={() => {
          locationAlerts({ location_id: selectedRow.id });
          setLocationDetails((oldValue) => {
            return { ...oldValue, location_id: selectedRow.id };
          });

          setBreadCrumbLabels((oldvalue) => {
            return { ...oldvalue, stateLabel: selectedRow.stateName };
          });
          setProgressState(1);
          const coordList = selectedRow.coordinates.replaceAll('"', '').split(',') || [];
          setCenterLatitude(parseFloat(coordList[0]));
          setCenterLongitude(parseFloat(coordList[1]));
        }}
      >
        {selectedRow.stateName}
      </h3>
    );
  }
  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setDataList(dataObject.data);
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
    setLocationCoordinationList(newArray);
    setZoomLevel(4);
  };

  const handleException = (errorObject) => {
  };

  const getBackgroundColor = (color, mode) => (mode === 'dark' ? darken(color, 0.6) : lighten(color, 0.1));

  const getHoverBackgroundColor = (color, mode) => (mode === 'dark' ? darken(color, 0.5) : lighten(color, 0.1));

  return (
    <>
      <Card className={'h-[40vh] xl:h-[35vh]'}
        sx={{
          boxShadow: 'none',
          borderRadius: '12px',
          boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px'
        }}
      >
        <Paper elevation={3} className={'h-full'} style={{ boxShadow: 'none' }}>
          <CardHeader
            title={
              <Breadcrumbs aria-label="breadcrumb" separator="›" fontSize='20px' fontWeight='600' >
                <h3 className='font-[customfont] font-semibold tracking-[1px] p-1 text-black text-[16px]'>
                  Location
                </h3>
              </Breadcrumbs>
            }
            sx={{ paddingBottom: 0 }}
          />
          <CardContent className={'h-[90%]'} style={{color:'black'}}>
            <DataGrid
              rows={dataList}
              columns={columns}
              loading={isLoading}
              pageSize={3}
              rowsPerPageOptions={[3]}
              // disableSelectionOnClick
              style={{
                // maxHeight: `${93}%`,
                border: 'none',
              }}
            />
          </CardContent>
        </Paper>
      </Card >
    </>
  );
}

export default LocationGridComponent;
