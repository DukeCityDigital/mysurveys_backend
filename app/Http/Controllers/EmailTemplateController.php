<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Project;
use Validator;
use App\Http\Controllers\MyProjectsController;
use App\Participant;
use App\ProjectParticipant;

class EmailTemplateController extends BaseController
{


    /**
     * Display a listing of the Email Templates
     *
     * @return \Illuminate\Http\Response
     */
    public function email_templates_with_project(Request $request)
    {

        $project_id =  $request->get('project_id');
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {
            $template->transformed = $this->transformEmailTemplateBodySubject($template, $project_id);
        }
        return $this->sendResponse($templates->toArray(), 'Email Templates Retrieved successfully.');
    }


    /**
     * Display a listing of the Email Templates
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {
            $template->transformed = $this->transformEmailTemplateBodySubject($template);
        }
        return $this->sendResponse($templates->toArray(), 'Email Templates Retrieved successfully.');
    }



    /**
     * Change the wildcards to their new values
     */
    public function transformEmailTemplateBodySubject($template, $project_id = 1, $user = null)
    {

        $body = $template['body'];
        $subject = $template['subject'];

        $project_data = Project::find($project_id);

        $replacements = array();
        if ($project_data) {
            $replacements[] = array('*projecttitle*', stripslashes($project_data->project_title));
            // $replacements[] = array('*link*', $userlink);
            $replacements[] = array('*responsibleperson*', stripslashes($project_data->responsible_person));
            $replacements[] = array('*projectinfo*', stripslashes($project_data->project_description));
            $replacements[] = array('*projectenddate*', $project_data->defaultend);
            $replacements[] = array('*maxpayout*', $project_data->max_payout);
            $replacements[] = array('*expectedpayout*',  $project_data->expected_payout);
        }
        $replacements[] = array('*contactaddress*', 'mysurveysteam@gmail.com');

        $seed_nick = "";
        $user_nick = "";
        $userlink = "";
 
        if ($user) {
            $pp = ProjectParticipant::where("participants_userid", $user->id)->first();
            $p = Participant::where("user_id", $user->id)->first();
            $user_nick = $p->nickname;
            
            if ($p && $p->seed_id) {
                $seed = Participant::where("user_id", $p->seed_id)->first();
                $seed_nick = $seed->nickname;
            }

            $replacements[] = array('*seednickname*', $seed_nick);

            if ($user && $project_data) {
                $pCtrl = new MyProjectsController();
                $proj = Project::find($project_id);
                if (!$pp) {
                    $pp = ProjectParticipant::where("projects_projectid", $project_id)->first();
                }
                $userlink = $pCtrl->makeProjectLink($pp, $proj);
            }
            $replacements[] = array('*username*', $user->email);
            $replacements[] = array('*nickname*', $user_nick);
        } else {
            $replacements[] = array('*username*', '<no user selected>');
            $replacements[] = array('*nickname*', '<no user selected>');
        }
        if (isset($template['password'])) {
            $replacements[] = array('*password*', $template['password']);
        }

        // REPLACE
        foreach ($replacements as $rep) {
            $subject = str_replace($rep[0], $rep[1], $subject);
            $body = str_replace($rep[0], $rep[1], $body);
        }
        // REPLACE


        // var_dump($body);
        return array(
            "body" => $body,
            "subject" => $subject,
            "link" => $userlink
        );
    }



    /**
     * Store a newly created Email Templates in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $input = $request->all();
        $validator = Validator::make($input, [
            'subject' => 'required|string',
            // 'body' => 'required|string'
        ]);
        if (!isset($input['body'])) {
            $input['body'] = "";
        }
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $template = EmailTemplate::create($input);
        return $this->sendResponse($template, 'Template created successfully.');
    }


    /**
     * Display the specified Email Templates.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified Email Templates.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Update the specified Email Templates in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $input = $request->all();
        $template = $emailTemplate;
        $data = array(
            "subject" => $input['subject'],
            "body" => $input['body'],

        );
        $template->update($data);
        return $this->sendResponse($template, 'Template updated successfully.', 200);
    }

    /**
     * Remove the specified Email Templates from storage.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
     
        $template = $emailTemplate;
        $template->delete();
    }
}
