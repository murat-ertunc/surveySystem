'use client';
import { useState, useEffect } from 'react';
import axios from 'axios';

interface Option {
  id: string;
  text: string;
  type?: 'column' | 'row';
}

interface Question {
  id: string;
  type: 'single_choice' | 'multiple_choice' | 'open_ended' | 'matrix';
  question_text: string;
  section: string;
  options: Option[];
}

interface Survey {
  questions?: Question[];
  [key: string]: unknown;
}

type Answer = [string, string]; // [questionId, answer]

export default function Home() {
  const [survey, setSurvey] = useState<Survey>({});
  const [questions, setQuestions] = useState<Question[]>([]);
  const [answers, setAnswers] = useState<Answer[]>([]);

  useEffect(() => {
    const fetchSurveyData = async () => {
      try {
        const response = await axios.post('http://localhost:3001/survey-data', {
          survey_id: 'GmvNVdRZbq',
          // survey_id: window.location.href.split('/').pop(),
        });
        setSurvey(response.data.data[0]);
      } catch (error) {
        console.error('Error fetching survey data:', error);
      }
    };
    fetchSurveyData();
  }, []);

  useEffect(() => {
    if (survey.questions) {
      setQuestions(survey.questions);
    }
  }, [survey]);

  const renderInnerQuestion = (question: Question) => {
    if (question.type === 'single_choice' || question.type === 'multiple_choice') {
      return (
        <div className="mt-4 space-y-2">
          {question.options.map((option, index) => (
            <div key={index} className="flex items-center">
              <input
                type={question.type === 'single_choice' ? 'radio' : 'checkbox'}
                id={option.id}
                name={question.id}
                onChange={(e) => saveAnswer(question.id, e.target.value)}
                value={option.id}
                className="mr-2"
              />
              <label htmlFor={option.id} className="text-gray-300">{option.text}</label>
            </div>
          ))}
        </div>
      );
    } else if (question.type === 'open_ended') {
      return (
        <div className="mt-4">
          <input
            type="text"
            id={question.id}
            name={question.id}
            className="w-full bg-gray-700 text-gray-300 p-2 rounded-lg"
          />
        </div>
      );
    } else {
      return (
        <div className="mt-4">
          <table className="w-full">
            <thead>
              <tr>
                {question.options
                  .filter((option) => option.type === 'column')
                  .map((option, index) => (
                    <th key={index} className="text-gray-300">{option.text}</th>
                  ))}
              </tr>
            </thead>
            <tbody>
              {question.options
                .filter((option) => option.type === 'row')
                .map((option, index) => (
                  <tr key={index}>
                    {question.options
                      .filter((option) => option.type === 'column')
                      .map((column, index) => (
                        <td key={index} className="text-gray-300">{column.text}</td>
                      ))}
                  </tr>
                ))}
            </tbody>
          </table>
        </div>
      );
    }
  };

  const saveAnswer = (questionId: string, answer: string) => {
    const question = questions.find((q) => q.id === questionId);
    if (question) {
      console.log('Question type:', question.type);
      setAnswers([...answers, [questionId, answer]]);
      console.log('Answers:', answers);
    }
  };

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center">
      <div className="mt-12 space-y-4 w-full max-w-screen-lg">
        {questions.map((question, index) => (
          <div
            key={index}
            className="w-full mx-auto bg-gray-800 p-6 rounded-lg shadow-lg"
          >
            <h2 className="text-xl font-semibold text-gray-300">
              Question {index + 1}
              <span className='text-gray-400 text-sm float-right'>
                ({question.section})
              </span>
            </h2>
            <p className="mt-4 text-gray-400">{question.question_text}</p>
            {renderInnerQuestion(question)}
          </div>
        ))}
      </div>
    </div>
  );
}
