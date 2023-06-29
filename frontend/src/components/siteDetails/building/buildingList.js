import React, { useState, useEffect } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { Edit, DeleteOutlined } from '@mui/icons-material';
import { Link, useLocation } from 'react-router-dom';
import { Breadcrumbs, Typography } from '@mui/material';
import { BuildingDeleteService, BuildingFetchService } from '../../../services/LoginPageService';
import { BuildingListToolbar } from './building-list-toolbars';
import BuildingModal from './BuildingModalComponent';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../context/UserAccessProvider';
import ApplicationStore from '../../../utils/localStorageUtil';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import { MdLocationPin } from 'react-icons/md';

export function BuildingListResults(props) {
  const dataColumns = [
    {
      field: 'buildingName',
      headerName: 'Building Name',
      width: 170,
      type: 'actions',
      align:'center',
      renderCell: ((params) => {
        return (
          <>
            <div className='flex w-full justify-between'>
              <div>
              <MdLocationPin className='text-[18px] mr-3' />
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
      field: 'buildingTotalFloors',
      headerName: 'Total Floors',
      width: 130,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'buildingTag',
      headerName: 'Building Tag',
      width: 230,
      align:'center',
      headerAlign: 'center'
    },
    {
      field: 'actions',
      type: 'actions',
      align:'center',
      headerName: 'Actions',
      width: 100,
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
  const { location_id, branch_id, facility_id } = routeStateObject.state;
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('location');

  const {locationLabel, branchLabel, facilityLabel,} = ApplicationStore().getStorage('siteDetails');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    setGridLoading(true);
    BuildingFetchService({
      location_id,
      branch_id,
      facility_id,
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
    }, 3000);
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
        to={`${props.selectedRow.buildingName}`}
        state={{
          location_id,
          branch_id,
          facility_id,
          building_id: props.selectedRow.id,
          buildingImg: props.selectedRow.buildingImg,
        }}
      >
        {props.selectedRow.buildingName}
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
          // BuildingDeleteService(props.selectedRow, deletehandleSuccess, deletehandleException);
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
    <div style={{  width: '100%', paddingBottom: '0px', marginTop: '0px' }}
    className='p-2 sm:p-5'
    >
      <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
        // height: '2vh',
        minHeight: '15px',
        fontFamily: 'customfont',
        fontWeight: '600',
        color: 'black',
        fontSize: '16px',
        letterSpacing: '1px'
      }} 
      // className='pb-20 min-[320px]:pb-20 min-[768px]:pb-0'
      >
        {locationLabel ? (
          <Typography
            underline="hover"
            color="inherit"
          >
            Location
          </Typography>
        ) : (
          <Link underline="hover" color="inherit" to="/Location">
            Location
          </Link>
        )}
        {branchLabel
          ? (
            <Typography
              underline="hover"
              color="inherit"
            >
              {pathname[1]}
            </Typography>
          )
          : (
            <Link
              underline="hover"
              color="inherit"
              to={`/Location/${pathname[1]}`}
              state={{
                location_id,
              }}
            >
              {pathname[1]}
            </Link>
          )}
        {facilityLabel
          ? (
            <Typography
              underline="hover"
              color="inherit"
            >
              {pathname[2]}
            </Typography>
          )
          : (
            <Link
              underline="hover"
              color="inherit"
              to={`/Location/${pathname[1]}/${pathname[2]}`}
              state={{
                location_id,
                branch_id,
              }}
            >
              {pathname[2]}
            </Link>
          )
        }
        <Typography
          underline="hover"
          color="inherit"
          sx={{ fontFamily: 'customfont', fontWeight: '600' }}
        >
          {pathname[3]}
        </Typography>
      </Breadcrumbs>

      <BuildingListToolbar
        setOpen={setOpen}
        setIsAddButton={setIsAddButton}
        setEditData={setEditData}
        userAccess={moduleAccess}
      />
      <DataGrid
        sx={{ border: 'none', fontFamily: 'customfont', color: 'black'}}
        rows={dataList}
        columns={dataColumns}
        pageSize={3}
        loading={isLoading}
        rowsPerPageOptions={[3]}
        disableSelectionOnClick
        style={{
          // maxHeight: `${80}%`,
          // height: '100%',
          // minHeight: '31vh',
          // minHeight: '330px',  
          height: '273px'
        }}
      />

      <BuildingModal
        isAddButton={isAddButton}
        editData={editData}
        open={open}
        setOpen={setOpen}
        locationId={location_id}
        branchId={branch_id}
        facilityId={facility_id}
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
        deleteService={BuildingDeleteService}
        handleSuccess={deletehandleSuccess}
        handleException={deletehandleException}
      />
    </div>
  );
}