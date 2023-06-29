import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { DeleteOutlined, Edit } from '@mui/icons-material';
import { Link, useLocation } from 'react-router-dom';
import { Breadcrumbs, Typography } from '@mui/material';
import { FacilityDeleteService, FetchFacilitiyService } from '../../../services/LoginPageService';
import { FacilityListToolbar } from './facility-list-toolbars';
import FacilityModal from './FacilityModalComponent';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../context/UserAccessProvider';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import ApplicationStore from '../../../utils/localStorageUtil';
import { MdLocationPin } from 'react-icons/md';

export function FacilityListResults(props) {
  const branchColumns = [
    {
      field: 'facilityName',
      headerName: 'Branch Name',
      width: 270,
      type: 'actions',
      renderCell: ((params) => {
        return (
          <>
            <div className='flex w-full justify-between'>
              <div>
              <MdLocationPin className='text-[18px] text-left' />
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
      width: 150,
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editData, setEditData] = useState([]);
  const [dataList, setDataList] = useState([]);
  const [isLoading, setGridLoading] = useState(false);
  const routeStateObject = useLocation();
  const { location_id, branch_id } = routeStateObject.state;
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('location');
  const {locationLabel, branchLabel} = ApplicationStore().getStorage('siteDetails');
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    setGridLoading(true);
    FetchFacilitiyService({
      location_id,
      branch_id,
    }, handleSuccess, handleException);
  }, [refreshData]);

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
    props.setLocationCoordinationList(newArray);
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
      setDeleteDailogOpen(false);
    }, 5000);
  };

  const deletehandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 3000);
  };

  function LinkTo(props) {
    return (
      <Link
        to={`${props.selectedRow.facilityName}`}
        state={{
          location_id,
          branch_id,
          facility_id: props.selectedRow.id,
          centerCoordination: props.selectedRow.coordinates
        }}
      >
        {props.selectedRow.facilityName}
      </Link>
    );
  }

  function EditData(props) {
    return (
      moduleAccess.edit
      && (
        <Edit
          onClick={() => {
            setIsAddButton(false);
            setEditData(props.selectedRow);
            setOpen(true);
          }}
          style={{ cursor: 'pointer' }}
        />
      ));
  }

  function DeleteData(props) {
    return moduleAccess.delete && (
      <DeleteOutlined
        onClick={() => {
          setDeleteId(props.selectedRow.id);
          setDeleteDailogOpen(true);
        }}
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
  const pathList = routeStateObject.pathname.split('/').filter((x) => x);
  const pathname = pathList.map((data, index) => {
    const path = data.replace(/%20/g, ' ');
    return (path);
  });
  return (
    <div style={{ width: '100%', padding: '20px', paddingBottom: '0', marginTop: '0px' }}>
      <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
        // height: '2vh',
        minHeight: '15px',
        fontFamily: 'customfont',
        fontWeight: '600',
        color: 'black',
        fontSize: '16px',
        letterSpacing: '1px'
      }}>
        {locationLabel ? (
          <Typography
            underline="hover"
            color="inherit"
            sx={{ fontFamily: 'customfont', fontWeight: '600' }}
          >
            Location
          </Typography>
        ) : (
          <Link underline="hover" color="inherit" to="/Location">
            Location
          </Link>
        )}
        {branchLabel ? (
          <Typography
            underline="hover"
            color="inherit"
            sx={{ fontFamily: 'customfont', fontWeight: '600', fontSize: '16px', letterSpacing: '1px' }}
          >
            {pathname[1]}
          </Typography>
        ) : (
          <Link
            underline="hover"
            color="inherit"
            to={`/Location/${pathname[1]}`}
            sx={{ fontFamily: 'customfont', fontWeight: '600', fontSize: '16px', letterSpacing: '1px' }}
            state={{
              location_id,
            }}
          >
            {pathname[1]}
          </Link>
        )}
        <Typography
          underline="hover"
          color="inherit"
          sx={{ fontFamily: 'customfont', fontWeight: '600', fontSize: '16px', letterSpacing: '1px' }}
        >
          {pathname[2]}
        </Typography>
      </Breadcrumbs>
      <FacilityListToolbar
        setOpen={setOpen}
        setIsAddButton={setIsAddButton}
        setEditData={setEditData}
        userAccess={moduleAccess}
      />
      <DataGrid
        sx={{ border: 'none', fontFamily: 'customfont', color: 'black', marginTop: '0px' }}
        rows={dataList}
        columns={branchColumns}
        pageSize={3}
        loading={isLoading}
        rowsPerPageOptions={[3]}
        disableSelectionOnClick
        style={{
          // maxHeight: `${80}%`,
          // height: '37vh' 
          // height: '100%',
          // // minHeight: '31vh',
          // minHeight: '230px',
          // maxHeight: '36vh'
          height: '230px',
        }}
      />

      <FacilityModal
        isAddButton={isAddButton}
        editData={editData}
        open={open}
        setOpen={setOpen}
        locationId={location_id}
        branchId={branch_id}
        setRefreshData={setRefreshData}
        locationCoordinationList={props.locationCoordinationList}
        centerCoord={{ lat: parseFloat(props.centerLat), lng: parseFloat(props.centerLng) }}
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
        deleteService={FacilityDeleteService}
        handleSuccess={deletehandleSuccess}
        handleException={deletehandleException}
      />
    </div>
  );
}