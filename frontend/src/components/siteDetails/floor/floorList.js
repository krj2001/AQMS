import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { DataGrid } from '@mui/x-data-grid';
import { PlayArrow, Edit, DeleteOutlined } from '@mui/icons-material';
import { Breadcrumbs, Stack, Typography } from '@mui/material';
import { FloorDeleteService, FloorfetchService } from '../../../services/LoginPageService';
import { FloorListToolbar } from './floor-list-toolbars';
import FloorModal from './FloorModalComponent';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { useUserAccess } from '../../../context/UserAccessProvider';
import ApplicationStore from '../../../utils/localStorageUtil';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import CentralHooterModal from '../CentralHooter/CentralHooterModal';
import { MdLocationPin } from 'react-icons/md';

export function FloorListResults({ img }) {
  const dataColumns = [
    {
      field: 'floorName',
      headerName: 'Floor Name',
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
      width: 100,
      cellClassName: 'actions',
      getActions: (params) => [
        <EditData selectedRow={params.row} />,
        <DeleteData selectedRow={params.row} />,
      ],
    },
  ];

  const [open, setOpen] = useState(false);
  const [openCentralHooter, setOpenCentralHooter] = useState(false);
  const [centralButtonText, setCentralButtonText] = useState('ADD CENTRAL HOOTER');
  const [colorValue,setColorValue] = useState("primary")
  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');
  const [isAddButton, setIsAddButton] = useState(true);
  const [editData, setEditData] = useState([]);
  const [dataList, setDataList] = useState([]);
  const [isLoading, setGridLoading] = useState(true);
  const routeStateObject = useLocation();
  const {
    location_id, branch_id, facility_id, building_id, buildingImg,
  } = routeStateObject.state;
  const [refreshData, setRefreshData] = useState(false);
  const moduleAccess = useUserAccess()('location');
  const {
    locationLabel, branchLabel, facilityLabel, buildingLabel,
  } = ApplicationStore().getStorage('siteDetails');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    setGridLoading(true);
    FloorfetchService({
      location_id,
      branch_id,
      facility_id,
      building_id,
    }, handleSuccess, handleException);
  }, [refreshData]);

  function LinkTo(props) {
    return (
      <Link
        to={`${props.selectedRow.floorName}`}
        state={{
          location_id,
          branch_id,
          facility_id,
          building_id,
          buildingImg,
          floor_id: props.selectedRow.id,
          floorMap: props.selectedRow.floorMap,
        }}
      >
        {props.selectedRow.floorName}
      </Link>
    );
  }

  const handleSuccess = (dataObject) => {
    setGridLoading(false);
    setDataList(dataObject.data);
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
  
  const pathList = routeStateObject.pathname.split('/').filter((x) => x);
  const pathname = pathList.map((data, index) => {
    const path = data.replace(/%20/g, ' ');
    return (path);
  });
  return (
    <div style={{ height: '50vh', width: '95%', paddingBottom: '0', marginTop: '0px', overflow: 'hidden' }}
    className='p-2 sm:p-5'
    >
      <Stack style={{
        // padding: '0 10px'
      }}
        // width={{
        //   xs: '100vw',
        //   sm: '100vw',
        //   md: '54vw',
        //   lg: '54vw',
        //   xl: '56vw'
        // }}
      >
        <Breadcrumbs aria-label="breadcrumb" separator="›" style={{
          // height: '2vh',
          // minHeight: '15px',
          fontFamily: 'customfont',
          fontWeight: '600',
          color: 'black',
          fontSize: '16px',
          letterSpacing: '1px',
          // minWidth: 'max-content'  // enable for scroll bar
        }}
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
            )}
          {buildingLabel ? (
            <Typography
              underline="hover"
              color="inherit"
            >
              {pathname[3]}
            </Typography>
          ) : (
            <Link
              underline="hover"
              color="inherit"
              to={`/Location/${pathname[1]}/${pathname[2]}/${pathname[3]}`}
              state={{
                location_id,
                branch_id,
                facility_id,
              }}
            >
              {pathname[3]}
            </Link>
          )}
          <Typography
            underline="hover"
            color="inherit"
            sx={{ fontFamily: 'customfont', fontWeight: '600' }}
          >
            {pathname[4]}
          </Typography>
        </Breadcrumbs>
      </Stack>

      <FloorListToolbar
        setOpen={setOpen}
        setIsAddButton={setIsAddButton}
        setEditData={setEditData}
        userAccess={moduleAccess}
      />

      <DataGrid
      className='mt-5 sm:mt-0'
        sx={{ border: 'none', fontFamily: 'customfont', color: 'black'}}
        rows={dataList}
        columns={dataColumns}
        pageSize={3}
        loading={isLoading}
        rowsPerPageOptions={[3]}
        disableSelectionOnClick
        style={{
          // maxHeight: `${80}%`,
          // height: '37vh'

          height: '300px',
          // minHeight: '57vh',
          // height: '78%',
          // minHeight: '250px',
          // maxHeight: '70vh'
        }}
      />

      <FloorModal
        isAddButton={isAddButton}
        editData={editData}
        open={open}
        setOpen={setOpen}
        locationId={location_id}
        branchId={branch_id}
        facilityId={facility_id}
        buildingId={building_id}
        setRefreshData={setRefreshData}
        src={img}
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
        deleteService={FloorDeleteService}
        handleSuccess={deletehandleSuccess}
        handleException={deletehandleException}
      />
    </div>
  );
}
