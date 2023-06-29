import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/DeleteOutlined';
import { Link } from 'react-router-dom';
import { FetchLocationService, LocationDeleteService } from '../../../services/LoginPageService';
import { LocationListToolbar } from './location-list-toolbars';
import LocationModal from './LocationModalComponent';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import { Card, CardContent, CardHeader, Divider } from '@mui/material';
import { MdLocationPin } from 'react-icons/md';

export function LocationListResults({ setLocationCoordinationList, centerLat, centerLng }) {
  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editState, setEditState] = useState([]);
  const [dataList, setDataList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('location');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const columns = [
    {
      field: 'stateName',
      headerName: 'Location Name',
      width: 300,
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
      field: 'actions',
      type: 'actions',
      headerName: 'Actions',
      width: 200,
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];
  useEffect(() => {
    setGridLoading(true);
    FetchLocationService(handleSuccess, handleException);
  }, [refreshData]);

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
  };

  const handleException = (errorObject) => {
  };

  const deletehandleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
    }, 5000);
  };

  const deletehandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  function LinkTo(props) {
    return (
      <Link
        to={`${props.selectedRow.stateName}`}
        state={{ location_id: props.selectedRow.id, centerCoordination: props.selectedRow.coordinates }}
      >
        {props.selectedRow.stateName}
      </Link>
    );
  }

  function EditData(props) {
    return (
      moduleAccess.edit
      && (
        <EditIcon
          onClick={() => {
            setIsAddButton(false);
            setEditState(props.selectedRow);
            setOpen(true);
          }}
          style={{ cursor: 'pointer' }}
        />
      ));
  }

  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteIcon
        onClick={() => {
            setDeleteId(props.selectedRow.id);
            setDeleteDailogOpen(true);
          }
        }
        style={{ cursor: 'pointer' }}
      />
    );
  }

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  return (
    <>
      <Card className='h-[50vh] sm:h-[42vh]'
        sx={{ boxShadow: 'rgba(100, 100, 111, 0.2) 0px 7px 29px 0px', borderRadius: '12px' }}>
        <CardHeader
          sx={{ padding: '16px', paddingBottom: '0', }}
          title={
            <>
              <LocationListToolbar
                setOpen={setOpen}
                setIsAddButton={setIsAddButton}
                setEditCustomer={setEditState}
                userAccess={moduleAccess}
              />
            </>
          }
        />
        <CardContent className='h-[350px] sm:h-[320px] lg:h-[85%]'>
          <DataGrid
            sx={{ border: 'none', fontFamily: 'customfont', color: 'black' }}
            rows={dataList}
            columns={columns}
            pageSize={3}
            loading={isLoading}
            rowsPerPageOptions={[3]}
            disableSelectionOnClick
            // className={'h-full min-h-[380px]'}
          />
          <LocationModal
            isAddButton={isAddButton}
            locationData={editState}
            open={open}
            setOpen={setOpen}
            setRefreshData={setRefreshData}
            centerCoord={{ lat: centerLat, lng: centerLng }}
          />
          <NotificationBar
            handleClose={handleClose}
            notificationContent={openNotification.message}
            openNotification={openNotification.status}
            type={openNotification.type}
          />
          <DeleteConfirmationDailog
            open={deleteDailogOpen}
            setOpen={setDeleteDailogOpen}
            deleteId={deleteId}
            deleteService={LocationDeleteService}
            handleSuccess={deletehandleSuccess}
            handleException={deletehandleException}
          />
        </CardContent>
      </Card>
    </>
  );
}
