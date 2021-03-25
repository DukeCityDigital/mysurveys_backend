<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Resources\Project as ProjectResource;
use App\Http\Controllers\BaseController;
use Validator;
use App\Researcher;
use App\User;
use Illuminate\Support\Facades\Auth;

class ProjectController extends BaseController
{

    /**
     * Display a listing of the Project.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $id = $request->user()->id;
        //        TODO filter by user id, future version
        $projects = Project::all();
        return $this->sendResponse(ProjectResource::collection($projects), 'Projects retrieved successfully.');
    }

    /**
     * Store a newly created Project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, ['project_title' => 'required|string']);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input['creator_userid'] = Auth::id();
        $p = Project::create($input);
        //        $p->researchers()->sync([Auth::id()]);TODO relate res to project
        return $this->sendResponse(new ProjectResource($p), 'Project created successfully.');
    }

    /**
     * Display the specified Project.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $project = Project::find($id);
        if (is_null($project)) {
            return $this->sendError('Setting not found.');
        }
        return $this->sendResponse(new ProjectResource($project), 'Project retrieved successfully.');
    }

    /**
     * Update the specified Project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $input = $request->all();
        $project = Project::find($id);
        $validator = Validator::make($input, Project::validator());
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input['defaultend'] = date('Y-m-d H:i:s', strtotime($input['defaultend']));
        $input['defaultstart'] = date('Y-m-d H:i:s', strtotime($input['defaultstart']));
        $project->update($input);
        return $this->sendResponse(new ProjectResource($project), 'Project updated successfully.', 200);
    }

    /**
     * Remove the specified Project from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Project::findOrFail($id)->delete();
        return $this->sendResponse([], 'Project deleted');
    }
}
